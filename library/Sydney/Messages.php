<?php

class Sydney_Messages extends Sydney_Singleton
{

    private $messages = array();

    /**
     *
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Add message
     * @param string|array $text
     */
    public function addMessage($text)
    {
        if (is_array($text)) {
            $this->messages = array_merge($this->messages, $text);
        } else {
            $this->messages = $text;
        }

        return $this;
    }

}
