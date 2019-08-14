<?php

class Smartassistant_Smartassistant_Helper_FtpConnector
{
    const ASCII = FTP_ASCII;

    const BINARY = FTP_BINARY;

    private $_host;
    private $_login;
    private $_password;
    private $_port = 21;
    private $_pasive = false;
    private $_mode = '0755';
    private $_defaultTransferMode = self::BINARY;
    private $_timeout = 30;
    private $_verbose = false;

    private $_connection;
    private $_currentPath;
    private $_ds = '/';
    private $_tmpFilePrefix = 'ftp_connector_';

    /**
     *
     * @param type $host
     * @param type $login
     * @param type $password
     * @param type $port
     * @param type $pasive
     */
    public function __construct($host, $login, $password, $port = 21, $pasive = false)
    {
        $this->_host = $host;
        $this->_login = $login;
        $this->_password = $password;
        $this->_port = $port;
        $this->_pasive = $pasive;
        $this->setCurrentPath('.');
    }

    /**
     * Check if FTP server is available with given configuration
     *
     * @return boolean
     */
    public function ping()
    {
        return ($this->connection() !== null);
    }

    /**
     * List directory
     *
     * @param string $path
     * @return boolean
     */
    public function ls($path = null)
    {
        $path = $path === null ? $this->_currentPath : $path;
        $path = rtrim($path, $this->_ds);
        $this->log('Listing location: ' . $path);
        if (($result=ftp_nlist($this->connection(), $path)) === false) {
            $this->log('Can not list location: ' . $path);
            return false;
        }

        $this->log('Listed location: ' . $path);
        return $result;
    }

    /**
     * Delete file from FTP
     *
     * @param string $filename
     * @param string $path
     * @return boolean
     */
    public function rmvFile($filename, $path = null)
    {
        $this->log('Removing file: ' . $filename);
        $path = $this->preparePath($filename, $path);
        $this->log('Real file path is: ' . $path);
        $success = ftp_delete($this->connection(), $path);

        if ($success) {
            $this->log('Removed file: ' . $path);
            return true;
        } else {
            $this->log('Can not remove file: ' . $path);
            return false;
        }
    }

    /**
     * Upload string to FTP as file
     *
     * @param string $destinationFileName
     * @param string $content
     * @param string $path
     * @param int $transferMode
     * @return boolean
     */
    public function putFileString($destinationFileName, $content, $path = null, $transferMode = self::BINARY)
    {
        if (($tmpName = tempnam(sys_get_temp_dir(), $this->_tmpFilePrefix)) === false) {
            $this->log('Prepare temporary file in: ' . $tmpName);
            return false;
        }

        if (! file_put_contents($tmpName, $content)) {
            $this->log('Can not created temporary file: ' . $tmpName);
            return false;
        }
        $this->log('Created temporary file: ' . $tmpName);

        $success = $this->putFile($destinationFileName, $tmpName, $path, $transferMode);

        if (@unlink($tmpName)) {
            $this->log('Removed temporary file: ' . $tmpName);
        } else {
            $this->log('Can not remove temporary file: ' . $tmpName);
        }

        return $success;
    }

    /**
     * Upload file to FTP
     *
     * @param string $destinationFileName
     * @param string $sourceFilePath
     * @param string $path
     * @param int $transferMode
     * @return boolean
     */
    public function putFile($destinationFileName, $sourceFilePath, $path = null, $transferMode = self::BINARY)
    {
        $this->log('Uploading file to: ' . $destinationFileName);
        $destinationFilePath = $this->preparePath($destinationFileName, $path);
        $this->log('Real file path is: ' . $destinationFilePath);
        if (! file_exists($sourceFilePath)) {
            $this->log('Can not read source file: ' . $sourceFilePath);
            return false;
        }
        $transferMode = $this->transferMode($transferMode);
        $success = ftp_put($this->connection(), $destinationFilePath, $sourceFilePath, $transferMode);
        if ($success) {
            $this->setPermissionsToFile($destinationFilePath);
            $this->log('File uploaded');
        } else {
            $this->log('Can not upload file');
        }
        return $success;
    }

    /**
     * Change current directory
     *
     * @param string $path
     * @throws Exception
     */
    public function chdir($path)
    {
        if (! ftp_chdir($this->connection(), $path)) {
            $this->log('Can not change directory to: ' . $path);
            return false;
        }
        $this->setCurrentPath(rtrim($path, '/'));
        $this->log('Current directory changet to: ' . $this->_currentPath);
    }

    /**
     * Set destination file permissions
     *
     * @param string $mode
     * @return boolean
     */
    public function setMode($mode)
    {
        if (strlen($mode) != 4) {
            $this->log('Wrong permissions. Will use default permissions.');
            return false;
        }
        $this->_mode = $mode;
        $this->log('Set permissions to: ' . $this->_mode);
        return true;
    }

    /**
     * Set transfer mode (ASCII or BINARY)
     *
     * @param int $transferMode
     */
    public function setDefaultTransferMode($transferMode)
    {
        if ($transferMode !== null && in_array($transferMode, array(self::ASCII, self::BINARY))) {
            $this->log('Cen not set default transfer mode to: ' . $transferMode);
            return true;
        }

        $this->log('Set default transfer mode to: ' . $this->_defaultTransferMode);
        $this->_defaultTransferMode = $transferMode;
    }

    /**
     * Set connection timelimit
     *
     * @param int $secounds
     */
    public function setTimeout($secounds)
    {
        $this->_timeout = $secounds;
        $this->log('Set timeout to: ' . $this->_timeout);
    }

    /**
     * Enable/Disable printing errors
     *
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->_verbose = (boolean) $verbose;
        $this->log('Set verbose to: ' . $this->_verbose);
    }

    /**
     * Prepare realpath of file
     *
     * @param strong $file
     * @param string $path
     * @return string
     */
    private function preparePath($file, $path = null)
    {
        $path = empty($path) ? $this->_currentPath : $path;
        $path = rtrim($path, $this->_ds) . '/' . $file;
        return $path;
    }

    /**
     * Set permissions to file on the FTP
     *
     * @param string $path
     * @throws Exception
     */
    private function setPermissionsToFile($path)
    {
        $this->log('Setting file permissions '.$this->_mode.' to ' . $path);
        if ($this->_mode !== null && ftp_chmod($this->connection(), octdec($this->_mode), $path) != true) {
            $this->log('Can not set permissions');
            return false;
        }
        $this->log('Can set successfully');
    }

    /**
     * Set current path
     *
     * @param string $path
     * @throws Exception
     */
    public function setCurrentPath($path)
    {
        $this->_currentPath = empty($path) ? '.' : $path;
    }

    /**
     * Receive transfer mode
     *
     * @param int $transferMode
     * @return int
     */
    private function transferMode($transferMode = null)
    {
        if ($transferMode !== null && in_array($transferMode, array(self::ASCII, self::BINARY))) {
            $this->log('Transfer mode: ' . $transferMode);
            return $transferMode;
        }
        $this->log('Transfer mode: ' . $this->_defaultTransferMode);
        return $this->_defaultTransferMode;
    }

    /**
     * Get FTP connection handler
     *
     * @return handler
     */
    private function connection()
    {
        if ($this->_connection === null) {
            if (! $this->connect() || ! $this->login()) {
                return null;
            }
        }

        return $this->_connection;
    }

    /**
     * Disconect with FTP
     *
     * @throws Exception
     */
    public function disconnect()
    {
        $this->log('Disconnecting');
        if (ftp_close($this->connection())) {
            $this->log('Disconnecting failed');
            return false;
        }
        $this->log('Disconnected');
        return true;
    }

    /**
     *
     * @throws Exception
     */
    public function connect()
    {
        $this->log('Connecting...');
        if (($this->_connection = ftp_connect($this->_host, $this->_port, $this->_timeout)) === false) {
            $this->log('Connecting failed');
            return false;
        }
        $this->log('Connected');
        return true;
    }

    /**
     * Connect to FTP
     *
     * @throws Excepion
     */
    public function login()
    {
        $this->log('Login to user: ' . $this->_login);
        if (! ftp_login($this->_connection, $this->_login, $this->_password)){
            $this->log('Login failed');
            return false;
        }
        $this->log('Login success');
        if (ftp_pasv($this->_connection, $this->_pasive)) {
            $this->log('Pasiv mode: ' . ($this->_pasive?'enabled':'disabled'));
        } else {
            $this->log('Can not set pasive mode');
        }

        return true;
    }

    /**
     * Put log
     *
     * @param string $message
     */
    private function log($message)
    {
        if ($this->_verbose) {
            echo $message . PHP_EOL;
        }
    }
}
