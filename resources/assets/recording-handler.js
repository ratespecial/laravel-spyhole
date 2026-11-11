const sendRecordings = (payload) => {
    if (
        !window.hasOwnProperty('spyholeConfig') ||
        !window.spyholeConfig.hasOwnProperty('storeUrl')
    )
        throw new Error('Missing spyhole configuration');

    payload['path'] = window.location.pathname;
    payload['type'] = window.spyholeConfig.type;

    fetch(window.spyholeConfig.storeUrl, {
        method: 'POST',
        body: JSON.stringify(payload),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.spyholeConfig.xsrf,
        },
    })
        .then((res) => res.json())
        .then((res) => {
            if (res.success) {
                window.spyholeDom.currentPage.recordingId = res.recordingId;
                if (document.getElementById(window.spyholeConfig.idValue).value === '') {
                    document.getElementById(window.spyholeConfig.idValue).value = res.recordingId;
                    // Emit a custom event after successfully saving the recording ID
                    const customEvent = new CustomEvent('spyholeRecordingSuccess', {
                        detail: {recordingId: res.recordingId},
                    });
                    window.dispatchEvent(customEvent);
                }
            }
        })
        .catch(() => {
            setTimeout(() => sendRecordings(payload), 500);
        });
};

const spyholeSendRemainingEvents = () => {
    if (window.spyholeEvents.length) {
        // Send remaining recordings
        let payload = {
            frames: window.spyholeEvents
        };
        window.spyholeEvents = [];
        if (window.spyholeDom.currentPage.recordingId !== null) {
            payload['recordingId'] = window.spyholeDom.currentPage.recordingId;
        }
        sendRecordings(payload);
    }
};

const initializeRecordings = () => {
    if (
        !(
            window.hasOwnProperty('spyholeConfig') &&
            window.spyholeConfig.hasOwnProperty('storeUrl') &&
            window.spyholeConfig.hasOwnProperty('samplingRate') &&
            window.spyholeConfig.hasOwnProperty('xsrf') &&
            window.hasOwnProperty('spyholeDom') &&
            window.spyholeDom.hasOwnProperty('domSent')
        )
    )
        throw new Error('Missing spyhole configuration');

    rrwebRecord.record({
        maskTextSelector: '[data-spyhole-mask]',
        emit(event) {
            if (!window.hasOwnProperty('spyholeEvents')) {
                window.spyholeEvents = [];
            }

            // push event into the events array
            if (event.type !== undefined && event.data !== undefined) {
                window.spyholeEvents.push(event);
            }

            if (
                window.spyholeEvents.length >= window.spyholeConfig.samplingRate
            ) {
                let payload = {
                    frames: window.spyholeEvents
                };
                window.spyholeEvents = [];
                if (!window.spyholeDom.domSent) {
                    payload['scene'] = document.documentElement.innerHTML;
                    window.spyholeDom.domSent = true;
                }
                if (window.spyholeDom.currentPage.recordingId !== null) {
                    payload['recordingId'] = window.spyholeDom.currentPage.recordingId;
                }
                sendRecordings(payload);
            }
        },
    });

    window.addEventListener('beforeunload', () => {
        spyholeSendRemainingEvents();
    });
};

(() => {
    initializeRecordings();
})();
