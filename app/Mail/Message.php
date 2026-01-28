<?php

namespace Phantom\Mail;

class Message
{
    protected $to = [];
    protected $subject;

    public function to($address, $name = null)
    {
        $this->to[$address] = $name;
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function getSubject()
    {
        return $this->subject;
    }
}
