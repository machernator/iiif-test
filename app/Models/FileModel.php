<?php
namespace Models;

class FileModel extends Model {

	/**
	 * Filedaten auslesen
	 *
	 * @param integer $fileid
	 * @return array
	 */
	public function getFile(int $fileid):array {
		$sp   = 'EXEC App.sp_Show_File ?';
        $params = [$fileid];
		$stmt = $this->executeResult($sp, $params);

        if($image = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			return $image;
		}
		return [];
	}

	/**
	 * File binär auslesen
	 *
	 * @param integer $fileid
	 * @return void
	 */
	public function getFileData(int $fileid)  {
		$sp   = 'EXEC App.sp_Show_File ?, 1';
        $params = [$fileid];
		$stmt = $this->executeResult($sp, $params);
		//sqlsrv_next_result($stmt);
        if(sqlsrv_fetch($stmt)) {
			$filedata = sqlsrv_get_field($stmt, 2, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY));
			fpassthru($filedata);
		}
		else {
			die("<p>Die Datei konnte nicht heruntergeladen werden: " . $this->getSqlerrors()['message'] . "</p>");
		}
	}
}
