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


class Document extends DatabaseObject {

	protected function defineRelationships() {}

	protected function overridePrimaryKeyName() {}


	//returns most recent signature date for this document
	public function getLastSignatureDate(){

		$query = "SELECT date_format(MAX(signatureDate), '%m/%d/%Y') lastSignatureDate
					FROM Signature S, Document D
					WHERE D.documentId = S.DocumentId
					AND (D.expirationDate is null || D.expirationDate = '0000-00-00')
					AND D.documentID = " . $this->documentID . "
					GROUP BY S.documentId
					ORDER BY 1;";

		$result = $this->db->processQuery($query, 'assoc');

		return $result['lastSignatureDate'];

	}





}

?>