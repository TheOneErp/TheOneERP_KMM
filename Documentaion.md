# Documentation

## Directory
* Front-end
    * Injects
    * Utils
    * Vue.directive
    * Vue.component
* Back-end
    * Injects
    * Utils
        1. [DatabaseUtil](#DatabaseUtil)
        2. [DataUtil](#DataUtil)
        3. [FileUtil](#FileUtil)
            * [Excel](#Excel)
            * [CSV](#CSV)
            * [JSON](#JSON)
        4. [LogUtil](#LogUtil)
        5. [MigrationUtil]()
        6. [ModelUtil]()
        7. [PageUtil](#PageUtil)
        8. [PermissionUtil](#PermissionUtil)
        9. [ReportUtil](#ReportUtil)
        10. [SessionUtil](#SessionUtil)
        11. [TranslationUtil](#TranslationUtil)
        12. [UserUtil](#UserUtil)
        13. [ValidationUtil](#ValidationUtil)
        14. [VerifyUtil](#VerifyUtil)
    * Controllers
    
* API

---

## Front-end (JS)

## Back-end (PHP/Laravel)

### DatabaseUtil
### DataUtil
### FileUtil
* ### Excel
    Use [box\spout](https://github.com/box/spout/blob/master/README.md)
    ```
    namespace App\Utils;
    
    protected $reader; // ReaderEntityFactory::createXLSXReader();
    protected $writer; // WriterEntityFactory::createXLSXWriter();
    protected $path; // The path of file;
    public $newFile = true; // To express whether the file is new.
    
    public function __constructor($src)
    // $src can be a "path" or Illuminate\Http\UploadedFile
    
    $excel = new Excel($path);
    ```
    #### methods
    1. getAllSheets
        
        It will return reader->getSheetIterator()
        Reference [Spout Documentation](https://opensource.box.com/spout/docs/)
        ```
        $excel->getAllSheets();
        ```
    2. array getAllSheetName
        
        It will return all names of sheets in an array
        ```
        $excel->getAllSheetName();
        ```
    3. array getSheetData
        
        It will be return specified sheet and specified datas(if data be specified).
        ```
        $excel->getSheetData($sheetId, $specific);
        ```
        $sheedId - Can be a number to specify sheet index(0-based), or a string to specify sheet name.
            
        $specific - Used to specify cells, it's an array, its format can be:
        
        1. 
        ```[
        ["A1","A2","B1", ...]
        ```
        2. 
        ```[
        [
            "A1" => "first name",
            "A2" => "last name", ...
        ]
        ```
            
        If $specific is an empty array, then it will return all datas of this sheet.

        In other words, it will return data which is specifiedn when the $specific is not empty.
            
        The format of return data:
        
        1. 
        ```[
        [
            ["A1", "A2", "A3"],
            ["B1", "B2", "B3"], ...
        ]
        ```
        
        2. 
        ```[
        [
            "A1" => data of A1,
            "A2" => data of A2, ...
        ]
        ```
        3. 
        ```[
        [
            "first name" => data of A1,
            "last name"  => data of A2
        ]
        
    4. array getAllSheetData
        
        To get all datas of all sheets
        ```
        $excel->getAllSheetData($toArray, $keyFromName)
        ```
        $toArray - True is default. When it is true, the return value will be an array, on the contrary, it will be Illuminate\Database\Eloquent\Collection.
        
        $keyFromName - True is default. When it is true, the sheet will be named by index, otherwise it will be name.
* ### CSV
* ### JSON
### LogUtil
### PageUtil
### PermissionUtil
### ReportUtil
### SessionUtil
### TranslationUtil
### UserUtil
### ValidationUtil
### VerifyUtil

## API