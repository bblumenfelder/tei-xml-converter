<?php


namespace App\ServiceClasses;


use Illuminate\Support\Facades\Mail;

abstract class AdminMailer {

    public static function sendSupportMessage(array $SupportMessage)
    {
        Mail::raw("Von " . $SupportMessage["from"] . ': ' . $SupportMessage["message"], function ($message) use($SupportMessage) {
            $message->to('bene_blumi@outlook.de')->subject($SupportMessage["browser"] . '||' . $SupportMessage["type"]);
        });
    }



    public static function requestUserRole(array $RequestMessage)
    {
        Mail::raw("Von " . $RequestMessage["from"] . ": \r\nIch möchte gerne " . $RequestMessage["role"] . " werden. \r\n" . $RequestMessage["message"], function ($message) use($RequestMessage) {
            $message->to('bene_blumi@outlook.de')->subject(
                $RequestMessage["from"] . ' möchte ' . $RequestMessage["role"] . ' werden.');
        });
    }
}