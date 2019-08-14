<?php

class Smartassistant_Smartassistant_Model_System_Config_Source_Ftpmodes
{
    /**
     * Retrieve list of ftp modes
     *
     * @return string
     */
    public function toOptionArray()
    {
        $modes = array(
            'ftp' => 'FTP',
            'ftps' => 'FTPS',
        );
        return $modes;
    }
}
