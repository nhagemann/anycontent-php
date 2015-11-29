<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;

use AnyContent\Connection\Abstracts\AbstractRecordsFileReadWrite;
use AnyContent\Connection\Interfaces\ReadOnlyConnection;

use AnyContent\Connection\Interfaces\WriteConnection;

use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;

class RecordsFileGitReadWriteConnection extends AbstractRecordsFileReadWrite implements ReadOnlyConnection, WriteConnection
{


    /**
     * @var GitWrapper
     */
    protected $wrapper;

    /** @var  GitWorkingCopy[] */
    protected $git = [ ];

    /**
     * @var int seconds not checking for remote changes
     */
    protected $confidence = 300;


    /**
     * @param $options (filenameRecords, filenameCMDL, repositoryUrl,repositoryPath, contentTypeName, confidence, fileNamePrivateKey)
     *
     * @return $this
     */
    public function addContentType($options)
    {
        $mandatory = [ 'filenameRecords', 'filenameCMDL', 'repositoryUrl', 'repositoryPath' ];

        $diff = array_diff($mandatory, array_keys($options));

        if (count($diff) > 0)
        {
            throw new AnyContentClientException ('Missing mandatory option(s): ' . join(', ', $diff));
        }

        $contentTypeName = basename($options['filenameCMDL'], '.cmdl');

        if (array_key_exists('contentTypeName', $options))
        {
            $contentTypeName = $options['contentTypeName'];
        }

        $contentTypeTitle = null;

        if (array_key_exists('contentTypeTitle', $options))
        {
            $contentTypeTitle = $options['contentTypeTitle'];
        }

        if (array_key_exists('confidence', $options))
        {
            $this->confidence = $options['confidence'];
        }

        $this->contentTypes[$contentTypeName] = [ 'json' => $options['filenameRecords'], 'cmdl' => $options['filenameCMDL'], 'definition' => false, 'records' => false, 'title' => $contentTypeTitle ];

        $wrapper = new GitWrapper();

        if (array_key_exists('fileNamePrivateKey', $options))
        {
            $wrapper->setPrivateKey($options['fileNamePrivateKey']);
        }

        if (file_exists($options['repositoryPath']))
        {
            $git = $wrapper->init($options['repositoryPath']);
        }
        else
        {
            $git = $wrapper->cloneRepository($options['repositoryUrl'], $options['repositoryPath']);
        }

        $this->wrapper = $wrapper;
        $this->git     = $git;

        return $this;
    }


    /**
     * @param $fileName
     *
     * @return \GuzzleHttp\Stream\StreamInterface|null
     * @throws ClientException
     */
    protected function readData($fileName)
    {
        $directory = $this->git->getDirectory();

        // http://stackoverflow.com/questions/2993902/how-do-i-check-the-date-and-time-of-the-latest-git-pull-that-was-executed
        $timestamp = 0;
        if (file_exists($directory . '/.git/FETCH_HEAD'))
        {
            $timestamp = exec('stat -c %Y ' . $directory . '/.git/FETCH_HEAD');
        }

        if (time() > ($timestamp + $this->confidence))
        {
            $this->git->pull();
        }

        return file_get_contents($directory . '/' . $fileName);
    }


    protected function writeData($fileName, $data)
    {
        $directory = $this->git->getDirectory();

        $this->git->pull();

        file_put_contents($directory . '/' . $fileName, $data);

        if ($this->git->hasChanges())
        {
            $this->git->commit('AnyContent Connection Commit');
            $this->git->push();
        }

        return true;
    }


    /**
     * @return GitWrapper
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }


    /**
     * @param GitWrapper $wrapper
     */
    public function setWrapper($wrapper)
    {
        $this->wrapper = $wrapper;
    }


    /**
     * @return \GitWrapper\GitWorkingCopy[]
     */
    public function getGit()
    {
        return $this->git;
    }


    /**
     * @param \GitWrapper\GitWorkingCopy[] $git
     */
    public function setGit($git)
    {
        $this->git = $git;
    }

}

