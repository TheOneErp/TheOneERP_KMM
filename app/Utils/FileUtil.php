<?php

namespace App\Utils;

use App\Utils\DataUtil;
use App\Utils\ValidationUtil;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class FileUtil
{
    public static $ALLOWED_FILE_EXTENSIONS = [
        'image' => ['bmp', 'gif', 'jpeg', 'jpg', 'svg', 'tiff', 'ico', 'png'],
        'video' => ['mp4', 'ogg', 'qtff', 'mpeg', 'mpg', 'mp4', 'avi', 'webm', '3gp'],
        'audio' => ['mpeg', 'mpg', 'mp3', 'ogg', 'aac', 'midi', 'wma', 'weba', '3gp',  'flac', 'aiff'],
        'document' => ['docx', 'doc', 'odt', 'dotx'],
        'spread_sheet' => ['xls', 'xlsx', 'ots', 'xltx'],
        'presentation' => ['ppt', 'pptx', 'odp', 'potx', 'pps', 'ppsx'],
        'pdf' => ['pdf'],
        'csv' => ['csv'],
        'archive' => ['rar', 'tar', 'zip', '7z', 'gz'],
        'text' => ['txt', 'rtf'],
        'other' => ''
    ];
    public static $ALLOWED_FILE_MIMETYPES = [
        'image' => ['image/bmp', 'image/gif', 'image/jpeg', 'image/svg+xml', 'image/tiff', 'image/x-icon', 'image/png'],
        'video' => ['video/mp4', 'video/ogg', 'video/quicktime', 'video/mpeg', 'video/mpeg4-generic', 'video/x-msvideo', 'video/webm', 'video/3gpp', 'video/3gpp2'],
        'audio' => ['audio/mpeg', 'audio/ogg', 'audio/aac', 'audio/midi', 'audio/x-midi', 'audio/wav', 'audio/weba', 'audio/3gpp', 'audio/3gpp2', 'audio/flac', 'audio/x-flac', 'audio/x-aiff'],
        'document' => ['application/msword', 'application/vnd.oasis.opendocument.text', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'spread_sheet' => ['application/vnd.ms-excel', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'presentation' => ['appliction/vnd.ms-poewrpoint', 'appliction/vnd.oasis.opendocument.presentation', 'appliction/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/vnd.ms-pps', 'application/mspowerpoint'],
        'pdf' => ['application/pdf'],
        'csv' => ['text/csv'],
        'archive' => ['application/x-rar.compressed', 'application/x-tar', 'application/zip', 'application/7z'],
        'text' => ['text/plain', 'application/rtf'],
        'other' => ''
    ];

    public static $DISALLOWED_FILE_EXTENSIONS = [
        "php", "phtml", // PHP Excuteable
        "asp", "aspx",   // ASP Excuteable
        "exe" // Windows Excuteable
    ];

    // Get file
    public static function getFile($fieldID, $id, $filename)
    {
        if (!FileUtil::checkFileExists($fieldID, $id, $filename))
            return false;
        return Storage::get("uploads/$fieldID/$id-$filename");
    }

    public static function getFileFromSaveRequest($tmpID, $fieldID)
    {
        if (isset(request()->{"file_" . $tmpID . "_" . $fieldID})) {
            return request()->{"file_" . $tmpID . "_" . $fieldID};
        }
        return null;
    }

    // PageControll save file logic
    public static function checkFilenames(&$data, $schema)
    {
        foreach ($schema['fields'] as $field) {
            if ($field['field_type'] == 'file') {
                FileUtil::checkFileName($data[$field['field_code']]);
            }
        }
    }

    public static function addUploadFile(&$array, $dataset, $schema)
    {
        foreach ($schema['fields'] as $field) {
            if ($field['field_type'] == 'file') {
                $file = FileUtil::getFileFromSaveRequest($dataset['tmpID'], $field['field_id']);
                if ($file != null) {
                    $array[] = [
                        'field_id' => $field['field_id'],
                        'id' => $dataset['data']['id'],
                        'file' => $file,
                        'filename' => $dataset['data'][$field['field_code']]
                    ];
                }
            }
        }
    }

    public static function addDeleteFile(&$array, $dataset, $oldData, $schema)
    {
        foreach ($schema['fields'] as $field) {
            if ($field['field_type'] == 'file') {
                if (!empty($oldData[$field['field_code']])) {
                    if ($dataset == null || FileUtil::getFileFromSaveRequest($dataset['tmpID'], $field['field_id']) != null)
                        $array[] = [
                            'field_id' => $field['field_id'],
                            'id' => $oldData['id'],
                            'filename' => $oldData[$field['field_code']]
                        ];
                }
            }
        }
    }

    public static function saveUploadFiles($files)
    {
        foreach ($files as $file) {
            FileUtil::saveFile($file['field_id'], $file['id'], $file['file'], $file['filename']);
        }
        return;
    }
    public static function deleteUploadedFiles($files)
    {
        foreach ($files as $file) {
            FileUtil::deleteFile($file['field_id'], $file['id'], $file['filename']);
        }
        return;
    }

    public static function saveFile($fieldID, $id, $file, $filename)
    {
        return $file->storeAs("uploads/$fieldID", "$id-$filename");
    }

    // Delete file
    public static function deleteFile($fieldID, $id, $filename)
    {
        if (!FileUtil::checkFileExists($fieldID, $id, $filename))
            return null;
        return Storage::delete("uploads/$fieldID/$id-$filename");
    }

    // Checker
    public static function checkFileExists($fieldID, $id, $filename)
    {
        return Storage::exists("uploads/$fieldID/$id-$filename");
    }
    public static function checkFileExistsByFilename($fieldID, $id, $filename)
    {
        return Storage::exists("uploads/$fieldID/$id-$filename");
    }
    public static function checkUploadFile(&$dataset, &$field)
    {
        if (!empty($dataset['data'][$field['field_code']])) {
            $file = FileUtil::getFileFromSaveRequest($dataset['tmpID'], $field['field_id']);
            if (FileUtil::checkFileName($dataset['field']) && FileUtil::checkFileType($file, $dataset, $field)) {
                return true;
            } else {
                return false;
            }
        }
        return null;
    }

    public static function checkFileName(&$filename)
    {
        $filename = str_replace('/', '', $filename);
        $filename = str_replace('\\', '', $filename);
        $filename = str_replace('*', '', $filename);

        $fileExtensions = explode('.', strtolower($filename));

        // 檢查有沒有包含不允許的副檔名
        foreach ($fileExtensions as $extension) {
            if (in_array($extension, FileUtil::$DISALLOWED_FILE_EXTENSIONS)) {
                return false;
            }
        }
        return true;
    }
    public static function checkFileType($file, $dataset, $field)
    {
        $mimeStatus = false;
        $extensionStatus = false;
        foreach ($field['field_options']['file_type'] as $type) {
            if(is_array($type)){
                if (in_array($file->getMimeType(), $type)) {
                    $mimeStatus = true;
                    $extensionStatus = true;
                }
            }else{
                $extensions = FileUtil::$ALLOWED_FILE_EXTENSIONS[$type];
                $mimeTypes = FileUtil::$ALLOWED_FILE_MIMETYPES[$type];
                if (in_array($file->getMimeType(), $mimeTypes)) {
                    $mimeStatus = true;
                }
                $fileExtensions = explode('.', $dataset['data'][$field['field_code']]);
                if (in_array(end($fileExtensions), $extensions)) {
                    $extensionStatus = true;
                }
            }
        }
        return $mimeStatus && $extensionStatus;
    }

    public static function newFolder(string ...$path)
    {
        foreach ($path as $p) {
            if (!file_exists($p)) {
                mkdir($p);
            }
        }
    }
}

class SpoutUitl
{
    protected $path;
    protected $format;
    protected $reader;
    protected $writer;
    protected $newFile = true;
    protected $readerOpening = false;
    protected $writerOpening = false;

    public function isNewFile()
    {
        return $this->newFile;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getAllRows($sheetId = null)
    {
        $this->reader->open($this->path);
        $sheet = null;
        if (is_null($sheetId) || $this->format === "csv") {
            $sheet = $this->reader->getSheetIterator()->current();
        } else {
            foreach ($this->reader->getSheetIterator() as $sh) {
                if (($sh->getName() === $sheetId) || ($sh->getIndex() === $sheetId)) {
                    $sheet = $sh;
                }
            }
        }

        $result = [];
        if (is_null($sheet)) {
            $this->reader->close();
            throw new Exception("The sheet named by $sheetId is not found.");
        }
        foreach ($sheet->getRowIterator() as $row) {
            $result[] = $row;
        }
        $this->reader->close();

        return $result;
    }

    public function getDataByRow(int $start = 0, int $end = -1, $sheetId = null)
    {
    }
    public function getDataByColumn(int $start = 0, int $end = -1, $sheetId = null)
    {
    }

    public function getValueFromRow(Row $row)
    {
        $result = [];
        foreach ($row->getCells() as $cellIndex => $cell) {
            $result[] = $cell->getValue();
        }
        return $result;
    }

    protected function init($src, string $format)
    {
        if (is_string($src)) {
            if (file_exists($src)) {
                $this->newFile = false;
                $this->path = $src;
            } else {
                $this->newFile = true;
            }
        } else if (is_a($src, "Illuminate\Http\UploadedFile")) {
            $this->newFile = false;
            $this->path = $src->path();
        } else {
            throw new Exception("Invalid source.");
        }

        $classes = [
            "xlsx" => "Excel",
            "csv" => "CSV"
        ];
        $class = "App\\Utils\\{$classes[$format]}";
       // dd($class,$src,$class::checkExtension($src));
        if(!$class::checkExtension($src)){
            throw new Exception("Invalid file format.");
        }

        $this->format = $format;
        $formatUpper = strtoupper($this->format);
        $reader = "create{$formatUpper}Reader";
        $writer = "create{$formatUpper}Writer";
        $this->reader = ReaderEntityFactory::{$reader}();
        $this->writer = WriterEntityFactory::{$writer}();
    }
}

class Excel extends SpoutUitl
{
    public function __construct($src)
    {
        $this->init($src, "xlsx");
    }

    public function getAllSheets()
    {
        $this->reader->open($this->path);
        $result = $this->reader->getSheetIterator();
        $this->reader->close();

        return $result;
    }

    public function getAllSheetName()
    {
        $result = [];
        $this->reader->open($this->path);
        foreach ($this->reader->getSheetIterator() as $sheet) {
            $result[] = $sheet->getName();
        }
        $this->reader->close();

        return $result;
    }

    public function getSheetData($sheetId, array $specific = [])
    {
        $result = [];
        $allData = [];
        $this->reader->open($this->path);
        foreach ($this->reader->getSheetIterator() as $sheet) {
            $sheetIndex = $sheet->getIndex();
            if (($sheet->getName() === $sheetId) || ($sheetIndex === $sheetId)) {
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $allData[] = $this->getValueFromRow($row);
                    /* foreach ($row->getCells() as $cellIndex => $cell) {
                        $allData[$rowIndex - 1][] = $cell->getValue();
                    } */
                }
            }
        }
        $this->reader->close();

        if (empty($specific)) {
            $result = $allData;
        } else {
            foreach ($specific as $key => $spec) {
                $specCell = empty(DataUtil::parseExcelCell($key)) ? DataUtil::parseExcelCell($spec) : DataUtil::parseExcelCell($key);
                if (!empty($specCell) && isset($allData[$specCell["row"]]) && isset($allData[$specCell["row"]][$specCell["column"]])) {
                    $result[$spec] = $allData[$specCell["row"]][$specCell["column"]];
                } else {
                    $result[$spec] = null;
                }
            }
        }

        return $result;
    }

    public function getAllSheetData(bool $toArray = true, bool $keyFromName = true)
    {
        $result = $toArray ? [] : collect([]);
        $this->reader->open($this->path);
        foreach ($this->reader->getSheetIterator() as $sheet) {
            $sheetData = $this->getSheetData($sheet->getIndex());
            if ($toArray) {
                $result[$keyFromName ? $sheet->getName() : $sheet->getIndex()] = $sheetData;
            } else {
                $result->push([
                    "name" => $sheet->getName(),
                    "datas" => collect($sheetData)
                ]);
            }
        }
        $this->reader->close();

        return $result;
    }

    static public function checkExtension($f)
    {      
        $result = false;
        $allowExtensions = [
            "xls",
            "xlsx",
            "xlsm"
        ];
    
        if (is_string($f)) {
            $result = file_exists($f) && in_array(pathinfo($f, PATHINFO_EXTENSION), $allowExtensions);
        } else if ($f instanceof \Illuminate\Http\UploadedFile) {
            // 改用 getClientOriginalExtension() 取得原始副檔名
            $result = in_array($f->getClientOriginalExtension(), $allowExtensions);
        }
    
        return $result;
    }
}

class CSV extends SpoutUitl
{
    protected $delimiter = ",";
    protected $enclosure = "";

    public function __construct($src)
    {
        $this->init($src, "csv");
    }

    public function getAllDatas()
    {
        $this->reader->open($this->path);
        $sheet = $this->reader->getSheetIterator()->current();
        $result = [];
        foreach ($sheet->getRowIterator() as $row) {
            $result[] = $this->getValueFromRow($row);
        }
        $this->reader->close();

        return $result;
    }

    public function writeDatas($datas, $bom = true)
    {
        $this->writer->openToFile($this->path);
        $this->writer->setShouldAddBOM($bom);
        $success = false;
        try {
            $this->writer->addRows($datas);
            $success = true;
        } catch (\Throwable $th) {
            if (is_array($datas) || DataUtil::isCollection($datas)) {
                foreach ($datas as $row) {
                    if (is_array($row) || DataUtil::isCollection($row)) {
                        $this->writer->addRow($row);
                        $success = true;
                    }
                }
            }
        }
        $this->writer->close();
        if (!$success) {
            throw new Exception("Invalid data format.");
        }
    }

    static public function checkExtension($f)
    {
        $result = false;

        if (is_string($f)) {
            $result = file_exists($f) && pathinfo($f, PATHINFO_EXTENSION) === "csv";
        } else if (is_a($f, "Illuminate\Http\UploadedFile")) {
            $result = $f->extension() === "csv";
        }

        return $result;
    }
}

class Json
{
    protected $id;
    protected $path;
    protected $datas;
    protected $autoDelete;
    protected $originalDatas;

    public function __construct($dataSrc, bool $autoDelete = false, $fileName = null)
    {
        if (DataUtil::isCollection($dataSrc)) {
            $dataSrc = $dataSrc->toArray();
            $this->datas = $dataSrc;
        } else if (is_array($dataSrc)) {
            $this->datas = $dataSrc;
        } else if (is_string($dataSrc)) {
            if (ValidationUtil::isJSONString($dataSrc)) {
                $this->datas = DataUtil::convertToArray(json_decode($dataSrc));
            } else {
                if (file_exists($dataSrc)) {
                    $this->path = $dataSrc;
                } else {
                    throw new Exception("The file is not found.");
                }
            }
        } else {
            throw new Exception("Invalid data format.");
        }

        if (is_null($fileName)) $fileName = uniqid();
        $this->id = $fileName;

        if (is_null($this->path)) {
            $path = "json/{$this->id}.json";
            $this->path = storage_path("app/$path");
            $content = json_encode($this->datas);
            Storage::disk("local")->put($path, $content);
        } else {
            $content = File::get($this->path);
            if (ValidationUtil::isJSONString($content)) {
                $this->datas = DataUtil::convertToArray(json_decode($content));
            } else {
                throw new Exception("Invalid file format.");
            }
        }
        $this->originalDatas = $this->datas;
        $this->autoDelete = $autoDelete;
    }

    public function __destruct()
    {
        if ($this->autoDelete) unlink($this->path);
    }

    public function update($datas)
    {
        $content = "";
        if (DataUtil::isCollection($datas)) {
            $content = $datas->toArray();
        } else if (is_array($datas)) {
            $content = $datas;
        } else if (is_string($datas) && ValidationUtil::isJSONString($datas)) {
            $content = DataUtil::convertToArray(json_decode($datas));
        } else {
            throw new Exception("Invalid data format.");
        }

        $this->datas = $content;
        return $this;
    }

    public function rollback()
    {
        $this->datas = $this->originalDatas;
    }

    public function save()
    {
        File::put($this->path, json_encode($this->datas));
        $this->originalDatas = $this->datas;
    }

    public function saveAs(string $path)
    {
        File::put($path, json_encode($this->datas));
        return new Json($path);
    }

    public function get()
    {
        return $this->datas;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getId()
    {
        return $this->id;
    }

    public function enableAutoDelete()
    {
        $this->autoDelete = true;
    }
    public function disableAutoDelete()
    {
        $this->autoDelete = false;
    }
}
