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


class ExpressionType extends DatabaseObject {

	protected function defineRelationships() {}

	protected function overridePrimaryKeyName() {}


	public function reorderTargets($targetsArray){
		$topTargetArray = array();
		$bottomTargetArray = array();
		$reorderedArray = array();

		$targetArray = array();
		foreach ($targetsArray as $i => $targetArray){
			if (count($this->getExpressionsByResource($targetArray['public_name'])) > 0){
				array_push($topTargetArray, $targetArray);
			}else{
				array_push($bottomTargetArray, $targetArray);
			}
		}

		$targetArray = array();
		foreach ($topTargetArray as $targetArray){
			array_push($reorderedArray, $targetArray);
		}


		foreach ($bottomTargetArray as $targetArray){
			array_push($reorderedArray, $targetArray);
		}

		return $reorderedArray;

	}


	//returns array of expression objects
	public function getExpressionsByResource($resourceName){

		$query = ("SELECT E.expressionID
					FROM Document D, SFXProvider SP, Expression E
					WHERE D.documentId = E.documentId
					AND (D.expirationDate is null || D.expirationDate = '0000-00-00')
					AND SP.documentID = D.documentID
					AND E.productionUseInd='1'
					AND E.expressionTypeID = '" . $this->expressionTypeID . "'
					AND SP.shortName = '" . $resourceName . "';");


		$result = $this->db->processQuery($query, 'assoc');

		$objects = array();

		//need to do this since it could be that there's only one request and this is how the dbservice returns result
		if (isset($result['expressionID'])){
			$object = new Expression(new NamedArguments(array('primaryKey' => $result['expressionID'])));
			array_push($objects, $object);
		}else{
			foreach ($result as $row) {
				$object = new Expression(new NamedArguments(array('primaryKey' => $row['expressionID'])));
				array_push($objects, $object);
			}
		}

		return $objects;
	}







	//returns array of expression type ids
	public function getExpressionTypesByResource($resourceName){

		$query = ("SELECT distinct E.expressionTypeID
					FROM Document D, SFXProvider SP, Expression E, ExpressionType ET
					WHERE D.documentId = E.documentId
					AND (D.expirationDate is null || D.expirationDate = '0000-00-00')
					AND SP.documentID = D.documentID
					AND E.productionUseInd='1'
					AND ET.expressionTypeID = E.expressionTypeID
					AND SP.shortName = '" . $resourceName . "'
					ORDER BY ET.shortName;");


		$result = $this->db->processQuery($query, 'assoc');

		$expressionTypeArray = array();

		//need to do this since it could be that there's only one result and this is how the dbservice returns result
		if (isset($result['expressionTypeID'])){
			$expressionTypeArray[] = $result['expressionTypeID'];
		}else{
			foreach ($result as $row) {
				$expressionTypeArray[] = $row['expressionTypeID'];
			}
		}

		return $expressionTypeArray;
	}







}

?>