<?php

namespace Eloquent;

class Email extends \Illuminate\Database\Eloquent\Model {

    /*
    ALTER TABLE `miserend`.`emails` 
    DROP COLUMN `timestamp`,
    ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `body`,
    ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00' AFTER `created_at`,
    ADD COLUMN `status` VARCHAR(45) NULL AFTER `updated_at`;
    */
    
    public $debug;
    public $debugger;
    
    
    function addToQueue() {
        $this->status = 'queued';
        return $this->save();        
    }
    
    function send($to = false) {
        if($to) $this->to = $to;
                        
        $this->status = 'sending';
        $this->save();
        /* mail() code */
        
        global $config;
        $this->debug = $config['mail']['debug'];
        $this->debugger = $config['mail']['debugger'];
        
        if ($this->debug == 1) {
            $this->header .= 'Bcc: ' . $this->debugger . "\r\n";
        } elseif ($this->debug == 2) {
            $this->body .= ".<br/>\n<i>Originally to: " . print_r($this->to, 1) . "</i>";
            $this->to = $this->debugger;
        }

        if (isset($this->subject) AND isset($this->body) AND isset($this->to)) {
            if ($this->debug == 3) {
                print_r($this);
            } else if ($this->debug == 5) {
                // black hole
            } else {
                if (!mail($this->to, $this->subject, $this->body, $this->header)) {
                    printr($this);
                    addMessage('Valami hiba történt az email elküldése közben.', 'danger');
                    $this->status = "error";
                    $this->save();
                } else {
                    $this->status = 'sent';
                    return $this->save();
                }
            }
        } else {
            $this->status = "error";
            $this->save();
            addMessage('Nem tudtuk elküldeni az emailt. Kevés az adat.', 'danger');
        }
        return false;
    }
    
    function __construct() {   
        global $config;
        
        $this->header = 'MIME-Version: 1.0' . "\r\n";
        $this->header .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
        $this->header .= 'From: ' . $config['mail']['sender'] . "\r\n";        
    }
    
}
