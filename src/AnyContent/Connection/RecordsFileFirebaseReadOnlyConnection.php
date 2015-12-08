<?php

namespace AnyContent\Connection;

use AnyContent\Connection\Abstracts\AbstractRecordsFileReadOnly;

use AnyContent\Connection\Interfaces\ReadOnlyConnection;

use Firebase\FirebaseLib;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class RecordsFileFirebaseReadOnlyConnection extends AbstractRecordsFileReadOnly implements ReadOnlyConnection
{

    /** @var  FirebaseLib */
    protected $firebase;

    protected $defaultPath;

    protected $maxNumberOfSingleRecordFetches = 5;

    protected $numberOfSingleRecordFetches = 0;


    public function selectFirebase($url, $token, $defaultPath)
    {
        $firebase       = new FirebaseLib($url, $token);
        $this->firebase = $firebase;
        $this->setDefaultPath($defaultPath);

    }


    public function addContentType($contentTypeName, $recordsKey, $cmdlKey, $contentTypeTitle = null)
    {

        $this->contentTypes[$contentTypeName] = [ 'json' => $recordsKey, 'cmdl' => $cmdlKey, 'definition' => false, 'records' => false, 'title' => $contentTypeTitle, 'recordsKey' => $recordsKey ];

        return $this;
    }


    /**
     * @return mixed
     */
    public function getDefaultPath()
    {
        return $this->defaultPath;
    }


    /**
     * @param mixed $defaultPath
     */
    public function setDefaultPath($defaultPath)
    {
        $path = trim($defaultPath, '/');
        if ($path == '')
        {
            $this->defaultPath = '/';
        }
        else
        {
            $this->defaultPath = '/' . $path . '/';
        }
    }


    /**
     * @return int
     */
    public function getMaxNumberOfSingleRecordFetches()
    {
        return $this->maxNumberOfSingleRecordFetches;
    }


    /**
     * @param int $maxNumberOfSingleRecordFetches
     */
    public function setMaxNumberOfSingleRecordFetches($maxNumberOfSingleRecordFetches)
    {
        $this->maxNumberOfSingleRecordFetches = $maxNumberOfSingleRecordFetches;
    }


    protected function readData($fileName)
    {

        $data = $this->firebase->get($this->getDefaultPath() . $fileName);

        return $data;
    }


    protected function readCMDL($fileName)
    {
        $data = $this->firebase->get($this->getDefaultPath() . $fileName);

        $data = json_decode($data);

        return $data;
    }


    /**
     * @return int
     * @throws AnyContentClientException
     */
    public function countRecords($contentTypeName = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if (!$this->hasLoadedAllRecords($contentTypeName))
        {
            // try to get the count information directly

            $path = $this->getDefaultPath() . $this->contentTypes[$contentTypeName]['recordsKey'] . '/info/count';
            $c    = json_decode($this->firebase->get($path));

            if ($c !== null)
            {
                return $c;
            }
        }

        return count($this->getAllRecords($contentTypeName));

    }


    /**
     * @param $recordId
     *
     * @return Record
     * @throws AnyContentClientException
     */
    public function getRecord($recordId)
    {
        $contentTypeName = $this->getCurrentContentTypeName();

        if (!$this->hasLoadedAllRecords($contentTypeName))
        {
            // try to get the record directly
            if ($this->numberOfSingleRecordFetches < $this->maxNumberOfSingleRecordFetches)
            {
                $this->numberOfSingleRecordFetches++;

                $path = $this->getDefaultPath() . $this->contentTypes[$contentTypeName]['recordsKey'] . '/records/' . $recordId;
                $data = $this->firebase->get($path);

                $data = json_decode($data, true);

                $record = $this->getRecordFactory()->createRecordFromJSONObject($this->getCurrentContentType(), $data);

                if ($record !== null)
                {
                    return $record;
                }

                throw new AnyContentClientException ('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());
            }

        }

        $records = $this->getAllRecords($this->getCurrentContentTypeName());

        if (array_key_exists($recordId, $records))
        {
            return $records[$recordId];
        }

        throw new AnyContentClientException ('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());
    }
}