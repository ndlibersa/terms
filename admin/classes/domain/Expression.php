<?php
/*
**************************************************************************************************************************
** CORAL Licensing Module Terms Tool Add-On v. 1.0
**
** Copyright (c) 2010 University of Notre Dame
**
** This file is part of CORAL.
**
** CORAL is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
**
** CORAL is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License along with CORAL.  If not, see <http://www.gnu.org/licenses/>.
**
**************************************************************************************************************************
*/


class Expression extends DatabaseObject {

	protected function defineRelationships() {}

	protected function overridePrimaryKeyName() {}


	//returns array of qualifier objects
	public function getQualifiers(){

		$query = "SELECT Qualifier.* FROM Qualifier, ExpressionQualifierProfile EQP where EQP.qualifierID = Qualifier.qualifierID AND expressionID = '" . $this->expressionID . "'";

		$result = $this->db->processQuery($query, 'assoc');

		$objects = array();

		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		if (isset($result['qualifierID'])){
			$object = new Qualifier(new NamedArguments(array('primaryKey' => $result['qualifierID'])));
			array_push($objects, $object);
		}else{
			foreach ($result as $row) {
				$object = new Qualifier(new NamedArguments(array('primaryKey' => $row['qualifierID'])));
				array_push($objects, $object);
			}
		}

		return $objects;
	}



	//returns array of Expression Note objects
	public function getExpressionNotes(){

		$query = "SELECT * FROM ExpressionNote where expressionID = '" . $this->expressionID . "' ORDER BY displayOrderSeqNumber";

		$result = $this->db->processQuery($query, 'assoc');

		$objects = array();

		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		if (isset($result['expressionNoteID'])){

			$object = new ExpressionNote(new NamedArguments(array('primaryKey' => $result['expressionNoteID'])));
			array_push($objects, $object);
		}else{
			foreach ($result as $row) {
				$object = new ExpressionNote(new NamedArguments(array('primaryKey' => $row['expressionNoteID'])));
				array_push($objects, $object);
			}
		}

		return $objects;
	}







	//returns most recent update date for this expression
	public function getLastUpdateDate(){

		$query = "SELECT date_format(MAX(updateDate), '%m/%d/%Y') lastUpdateDate FROM (
					SELECT MAX(lastUpdateDate) updateDate
						FROM Expression WHERE expressionID='" . $this->expressionID . "'
					UNION
					SELECT MAX(lastUpdateDate) updateDate
						FROM ExpressionNote WHERE expressionID='" . $this->expressionID . "') allDates;";

		$result = $this->db->processQuery($query, 'assoc');

		return $result['lastUpdateDate'];

	}


}

?>
