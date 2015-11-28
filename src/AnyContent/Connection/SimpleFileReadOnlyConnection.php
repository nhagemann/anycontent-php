<?php

namespace AnyContent\Connection;

use AnyContent\AnyContentClientException;
use AnyContent\Connection\Interfaces\SimpleReadOnlyConnection;

use AnyContent\Connection\Traits\CMDLCache;
use AnyContent\Connection\Traits\CMDLParser;
use AnyContent\Connection\Traits\Factories;
use AnyContent\Connection\Traits\Logger;

use AnyContent\Repository\Record;
use CMDL\ContentTypeDefinition;

use Symfony\Component\Filesystem\Filesystem;

class SimpleFileReadOnlyConnection implements SimpleReadOnlyConnection
{

    use CMDLParser;
    use Logger;
    use Factories;
    use CMDLCache;

    protected $currentContentTypeName = null;

    /** @var  ContentTypeDefinition */
    protected $currentContentTypeDefinition = null;

    /**
     * @var array  [ 'json' => , 'cmdl' => , 'definition' => , 'records' => ]
     */
    protected $contentTypes = [ ];


    /**
     * @param      $filenameRecords
     * @param      $filenameCMDL
     * @param null $contentTypeName
     * @param null $contentTypeTitle
     *
     * @return $this
     * @throws AnyContentClientException
     */
    public function addContentTypeFile($filenameRecords, $filenameCMDL, $contentTypeName = null, $contentTypeTitle = null)
    {
        $fs = new Filesystem();

        if (!$fs->exists($filenameRecords))
        {
            throw new AnyContentClientException('File ' . $filenameRecords . ' not found.');
        }
        if (!$fs->exists($filenameCMDL))
        {
            throw new AnyContentClientException('File ' . $filenameRecords . ' not found.');
        }

        if ($contentTypeName == null)
        {
            $contentTypeName = basename($filenameCMDL, '.cmdl');
        }

        $this->contentTypes[$contentTypeName] = [ 'json' => $filenameRecords, 'cmdl' => $filenameCMDL, 'definition' => false, 'records' => false, 'title' => $contentTypeTitle ];

        return $this;
    }


    /**
     * @param $contentTypeName
     *
     * @return \CMDL\ConfigTypeDefinition|ContentTypeDefinition|\CMDL\DataTypeDefinition|null
     * @throws AnyContentClientException
     * @throws \CMDL\CMDLParserException
     */
    public function getContentTypeDefinition($contentTypeName)
    {
        if ($this->getCMDLCache()->contains($contentTypeName))
        {
            return $this->getCMDLCache()->fetch($contentTypeName);
        }

        if (array_key_exists($contentTypeName, $this->contentTypes))
        {
            if ($this->contentTypes[$contentTypeName]['definition'] !== false)
            {
                return $this->contentTypes[$contentTypeName]['definition'];
            }

            $cmdl = $this->readData($this->contentTypes[$contentTypeName]['cmdl']);

            if ($cmdl)
            {

                $parser = $this->getParser();

                $definition = $parser->parseCMDLString($cmdl, $contentTypeName, $this->contentTypes[$contentTypeName]['title']);

                if ($definition)
                {
                    $this->contentTypes[$contentTypeName]['definition'] = $definition;
                    $this->getCMDLCache()->save($contentTypeName, $definition, $this->cacheDuration);

                    return $definition;
                }
            }

        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);
    }


    /**
     * @return array
     */
    public function getContentTypeNames()
    {
        return array_keys($this->contentTypes);
    }


    /**
     * @return ContentTypeDefinition[]
     * @throws AnyContentClientException
     */
    public function getContentTypes()
    {
        $contentTypes = [ ];
        foreach ($this->contentTypes as $contentTypeName => $item)
        {
            $definition                           = $this->getContentTypeDefinition($contentTypeName);
            $contentTypes[$definition->getName()] = $definition;
        }

        return $contentTypes;

    }


    /**
     * @param $contentTypeName
     *
     * @return $this
     * @throws AnyContentClientException
     */
    public function selectContentType($contentTypeName)
    {
        $definition = $this->getContentTypeDefinition($contentTypeName);

        $this->currentContentTypeDefinition = $definition;
        $this->currentContentTypeName       = $definition->getName();

        return $this;
    }


    /**
     * @return ContentTypeDefinition
     * @throws AnyContentClientException
     */
    public function getCurrentContentType()
    {
        if ($this->currentContentTypeDefinition == null)
        {
            throw new AnyContentClientException('No content type selected.');
        }

        return $this->currentContentTypeDefinition;

    }


    /**
     * @return string
     * @throws AnyContentClientException
     */
    public function getCurrentContentTypeName()
    {
        if ($this->currentContentTypeName == null)
        {
            throw new AnyContentClientException('No content type selected.');
        }

        return $this->currentContentTypeName;
    }


    /**
     * @return int
     * @throws AnyContentClientException
     */
    public function countRecords($contentTypeName=null)
    {
        return count($this->getAllRecords($contentTypeName));
    }


    /**
     * @param null $contentTypeName
     *
     * @return Record[]
     * @throws AnyContentClientException
     */
    public function getAllRecords($contentTypeName = null)
    {
        if ($contentTypeName == null)
        {
            $contentTypeName = $this->getCurrentContentTypeName();
        }

        if (array_key_exists($contentTypeName, $this->contentTypes))
        {

            if ($this->contentTypes[$contentTypeName]['records'] !== false)
            {
                return $this->contentTypes[$contentTypeName]['records'];
            }

            $data = $this->readData($this->contentTypes[$contentTypeName]['json']);

            if ($data)
            {
                $data = json_decode($data, true);

                $definition = $this->getContentTypeDefinition($contentTypeName);

                $records = $this->getRecordFactory()
                                ->createRecordsFromJSONArray($definition, $data['records']);

                $this->contentTypes[$contentTypeName]['records'] = $records;

                return $records;
            }

        }

        throw new AnyContentClientException ('Unknown content type ' . $contentTypeName);

    }


    protected function readData($fileName)
    {
        return file_get_contents($fileName);
    }


    /**
     * @param $recordId
     *
     * @return Record
     * @throws AnyContentClientException
     */
    public function getRecord($recordId)
    {
        $records = $this->getAllRecords($this->getCurrentContentTypeName());

        if (array_key_exists($recordId, $records))
        {
            return $records[$recordId];
        }

        throw new AnyContentClientException ('Record ' . $recordId . ' not found for content type ' . $this->getCurrentContentTypeName());
    }
}