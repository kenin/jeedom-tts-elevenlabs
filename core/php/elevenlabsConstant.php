<?php

class ElevenlabsConstant
{
    public static $MP3_SYSTEM_PATH = __DIR__ . '/../../data/';
    public static $MP3_PLUGIN_PATH = '/plugins/elevenlabs/data/';

    public static $BASEAPI_URL = "https://api.elevenlabs.io/v1/";
    public static $VOICES_API = "voices";
    public static $TTS_API = "text-to-speech/";
    public static $MODELS = ["eleven_multilingual_v2","eleven_multilingual_v1"];
    public static $DEFAULT_MODEL = "eleven_multilingual_v2";
}

?>