<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule\WebTests;

/**
 * Class for retrieving data from MailHog
 */
class MailHogApi
{
    public const MAIL_HOG_API_URL = 'http://localhost:8025/api/v2/';

    private $apiConnection;

    public function __construct()
    {
        // $apiConnection = curl_init(self::MAIL_HOG_API_URL);
    }

    public function getMessages($toEmailAddress = null, $messageSubject = null)
    {
        $messages = array();

        $headers = array(
            'Accept: application/json',
            'Content-Type: application/json'
        );
 
        $connection = curl_init();

        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connection, CURLOPT_HEADER, 0);

        curl_setopt($connection, CURLOPT_URL, self::MAIL_HOG_API_URL . 'messages');
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);

        $jsonResults = curl_exec($connection);
        $results = json_decode($jsonResults);

        if (count($results->items) > 0) {
            foreach ($results->items as $item) {
                # print "\n==================================================\n";
                # print_r($item->Content->Headers);

                $message = new EmailMessage();

                $message->setSubject($item->Content->Headers->Subject[0]);

                if (array_key_exists('Date', $item->Content->Headers)) {
                    $message->setDate($item->Content->Headers->Date[0]);
                }

                $from = $item->From->Mailbox . '@' . $item->From->Domain;
                $message->setFrom($from);

                $toEmails = array();
                foreach($item->To as $toInfo) {
                    $toEmail = $toInfo->Mailbox . '@' . $toInfo->Domain;
                    $toEmails[] = $toEmail;
                }
                $message->setTo($toEmails);

                $message->setMessage($item->Content->Body);

                # print "\n-------------------------------------------------------------------\n";
                # print_r($item);

                if ($toEmailAddress !== null && !in_array($toEmailAddress, $toEmails)) {
                    ; // don't store message
                } elseif ($messageSubject !== null && $messageSubject !== $message->getSubject()) {
                    ; // don't store message
                } else {
                    $messages[] = $message;
                }
            }
        }

        curl_close($connection);

        return $messages;
    }
}

require __DIR__ . '/../vendor/autoload.php';

$api = new MailHogApi();
$api->getMessages();
