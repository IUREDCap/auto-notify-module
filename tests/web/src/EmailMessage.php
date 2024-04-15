<?php

#-------------------------------------------------------
# Copyright (C) 2023 The Trustees of Indiana University
# SPDX-License-Identifier: BSD-3-Clause
#-------------------------------------------------------

namespace IU\AutoNotifyModule\WebTests;

/**
 * E-mail message class.
 */
class EmailMessage
{
    private $from;
    private $to;
    private $subject;
    private $date;
    private $message;


    public function __construct()
    {
        $this->from    = '';
        $this->to      = array();
        $this->subject = '';
        $this->date    = '';
        $this->message = '';
    }

    public function getMessageHtml()
    {
        $htmlMessage = $this->message;
        $htmlMessage = preg_replace('/^.*<html>/s', '<html>', $htmlMessage);
        $htmlMessage = preg_replace('/<\/html>.*$/s', "</html>\n", $htmlMessage);
        return $htmlMessage;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from)
    {
        $this->from = $from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to)
    {
        $this->to = $to;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }
}
