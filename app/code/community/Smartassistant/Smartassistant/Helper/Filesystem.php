<?php

class Smartassistant_Smartassistant_Helper_Filesystem extends Mage_Core_Helper_Abstract
{
    /**
     * Open / create file to write
     *
     * @param string $path Path of file
     * @return handle
     */
    public function openFile($path)
    {
        $handle = fopen($path, 'a');
        return $handle;
    }

    /**
     * Write csv line with given fields
     *
     * @param handle $handle
     * @param array $fields
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escapeChar
     * @return boolean
     */
    public function putCsvLine($handle, $fields, $delimiter, $enclosure, $escapeChar)
    {
        $success = (fputcsv($handle, $fields, $delimiter, $enclosure) !== false);
        return $success;
    }

    /**
     *
     * @param handle $handle
     */
    public function closeFile($handle)
    {
        fclose($handle);
    }

    /**
     * Retreive directory containing export files
     *
     * @return string|boolean
     */
    public function getExportsDir()
    {
        $smarassistantDir = $this->getSmartassistantDir();
        $dir = rtrim($smarassistantDir, DS) . DS . 'exports/';
        if (! is_dir($dir) && ! mkdir($dir)) {
            return false;
        }
        return $dir;
    }

    /**
     * Retreive media subdirectory for this module
     *
     * @return string|boolean
     */
    public function getSmartassistantDir()
    {
        $media = Mage::getBaseDir('media');
        $dir = rtrim($media, DS) . DS . 'smartassistant/';
        if (! is_dir($dir) && ! mkdir($dir)) {
            return false;
        }
        return $dir;
    }
}
