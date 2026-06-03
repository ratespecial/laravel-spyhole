<?php

namespace Ratespecial\LaravelSpyhole\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SessionRecording
 *
 * @property int id
 * @property string path
 * @property string type
 * @property string session_id
 * @property array recordings
 * @package Ratespecial\LaravelSpyhole\Models
 */
class SessionRecording extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'session_recordings';

    public function getRecordingsAttribute()
    {
        return json_decode($this->attributes['recordings']);
    }

    public function setRecordingsAttribute($value)
    {
        $this->attributes['recordings'] = json_encode($value);
    }
}
