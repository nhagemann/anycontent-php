<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;

use AnyContent\Connection\Abstracts\AbstractRecordsFileReadWrite;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;

use AnyContent\Connection\Interfaces\UniqueConnection;
use AnyContent\Connection\Interfaces\WriteConnection;

use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class RecordsFileGitReadWriteConnection extends AbstractRecordsFileReadWrite implements ReadOnlyConnection, WriteConnection, UniqueConnection
{

    /**
     * @var GitWrapper
     */
    protected $wrapper;

    /** @var  GitWorkingCopy[] */
    protected $git = [ ];

    protected $uniqueConnection = false;

    /**
     * @var int seconds not checking for remote changes
     */
    protected $confidence = 300;

    /**
     * @var string url of the remote git repository
     */
    protected $remoteUrl;

    /**
     * @var string directory containing the local copy of the git repository
     */
    protected $directory;


    /**
     * @param $options (filenameRecords, filenameCMDL, repositoryUrl,repositoryPath, contentTypeName, confidence, fileNamePrivateKey)
     *
     * @return $this
     */
    public function addContentType($contentTypeName, $filenameCMDL, $filenameRecords, $contentTypeTitle = null)
    {
        $this->contentTypes[$contentTypeName] = [ 'json' => $filenameRecords, 'cmdl' => $filenameCMDL, 'definition' => false, 'records' => false, 'title' => $contentTypeTitle ];

        return $this;
    }


    protected function readRecords($fileName)
    {
        try
        {
            return $this->readData($fileName);
        }
        catch (FileNotFoundException $e)
        {

        }

        return json_encode([ 'records' => [ ] ]);
    }


    protected function readData($fileName)
    {

        $directory = $this->getDirectory();

        $this->occasionalPull();

        if (!file_exists($directory . '/' . $fileName))
        {
            throw new FileNotFoundException($directory . '/' . $fileName);
        }

        return file_get_contents($directory . '/' . $fileName);

    }


    protected function writeData($fileName, $data)
    {
        $directory = $this->getDirectory();

        if ($this->isUniqueConnection())
        {

            $this->occasionalPull();
        }
        else
        {
            $this->getGit()->pull();
        }

        file_put_contents($directory . '/' . $fileName, $data);

        if ($this->getGit()->hasChanges())
        {
            $this->getGit()->commit('AnyContent Connection Commit');
            $this->getGit()->push();
        }
        //@upgrade force pull on error
        if ($this->getGit()->hasChanges())
        {
            $this->getGit()->add('*.json');
            $this->getGit()->commit('AnyContent Connection Add Commit');
            $this->getGit()->push();
        }

        return true;
    }


    protected function occasionalPull()
    {
        $directory = $this->getDirectory();

        // http://stackoverflow.com/questions/2993902/how-do-i-check-the-date-and-time-of-the-latest-git-pull-that-was-executed
        $timestamp = 0;
        if (file_exists($directory . '/.git/FETCH_HEAD'))
        {
            $timestamp = exec('stat -c %Y ' . $directory . '/.git/FETCH_HEAD');
        }

        if (time() > ($timestamp + $this->confidence))
        {
            $this->getGit()->pull();
        }
    }


    /**
     * @return boolean
     */
    public function isUniqueConnection()
    {
        return $this->uniqueConnection;
    }


    public function setUniqueConnection($confidence = 60)
    {
        $this->confidence       = (int)$confidence;
        $this->uniqueConnection = (boolean)$confidence;
    }


    /**
     * @return string
     */
    public function getRemoteUrl()
    {
        return $this->remoteUrl;
    }


    /**
     * @param string $remoteUrl
     */
    public function setRemoteUrl($remoteUrl)
    {
        $this->remoteUrl = $remoteUrl;
    }


    /**
     * @return mixed
     */
    public function getDirectory()
    {
        if ($this->directory == '')
        {
            throw new AnyContentClientException ('No git working directory set.');
        }

        return rtrim($this->directory, '/');
    }


    /**
     * @param mixed $directory
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }


    /**
     * @return GitWrapper
     */
    public function getWrapper()
    {
        if (!$this->wrapper)
        {
            $this->wrapper = new GitWrapper();
        }

        return $this->wrapper;
    }


    /**
     * @param GitWrapper $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
    }


    /** @return GitWorkingCopy */
    public function getGit()
    {
        if (!$this->git)
        {
            if (file_exists($this->getDirectory()))
            {
                $this->git = $this->getWrapper()->init($this->getDirectory());
            }
            else
            {

                $this->git = $this->getWrapper()->cloneRepository($this->getRemoteUrl(), $this->getDirectory());
            }
        }

        return $this->git;
    }


    /**
     * @param GitWorkingCopy $git
     */
    public function setGit($git)
    {
        $this->git = $git;
    }


    public function setPrivateKey($filename)
    {
        $this->getWrapper()->setPrivateKey($filename);

        return $this;
    }
}

