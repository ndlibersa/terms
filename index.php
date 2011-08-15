<?php

// variables for permitted/prohibited qualifiers (this should match up with the available qualifiers in the license module)
//permitted terms will display green checkbox
$permittedQualifier = 'Permitted';

//prohibited terms will display red x
$prohibitedQualifier = 'Prohibited';

/*
**************************************************************************************************************************
** CORAL Licensing Module Terms Tool Add-On Terms Tool Add-On v. 1.0
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

include_once 'directory.php';

//get the passed in ISSN or ISBN
if (isset($_GET['issn'])) $issn = $_GET['issn']; else $issn='';
if (isset($_GET['isbn'])) $isbn = $_GET['isbn']; else $isbn='';


//either isbn or isn must be passed in
if (($isbn == '') && ($issn == '')){
	$displayHTML = 'You must pass in either ISSN or ISBN.';
}else{

	try{
		//get targets from the terms tool service for this ISBN or ISSN
		$termsServiceObj = new TermsService(new NamedArguments(array('issn' => $issn, 'isbn' => $isbn)));
		$termsToolObj = $termsServiceObj->getTermsToolObj();


		$targetsArray = array();
		$targetsArray = $termsToolObj->getTargets();


		if (count($targetsArray) == 0){
			$displayHTML = "Sorry, no Full Text Providers are available for this ISSN/ISBN";
		}else{

			//if expression type ID is passed in, display the terms
			if (isset($_GET['typeID'])){
				$typeID = $_GET['typeID'];
				$expressionType = new ExpressionType(new NamedArguments(array('primaryKey' => $typeID)));

				$pageTitle = $expressionType->shortName . " License Terms";
				$displayHTML = "<div class='darkShaded' style='width:664px; padding:8px; margin:0 0 7px 0;'><span class='headerText'>" . $expressionType->shortName . " Terms</span>&nbsp;&nbsp;for " . $termsToolObj->getTitle() . "</div>";


				$displayHTML .= "<div style='margin-left:5px;'>";

				$orderedTargetsArray = array();
				$orderedTargetsArray = $expressionType->reorderTargets($targetsArray);

				$targetArray = array();
				foreach ($orderedTargetsArray as $i => $targetArray){
					$displayHTML .= "<span class='titleText'>" . $targetArray['public_name'] . "</span><br />";

					$expressionArray = array();
					$expressionArray = $expressionType->getExpressionsByResource($targetArray['public_name']);

					//if no expressions are defined for this resource / expression type combination
					if (count($expressionArray) == '0'){
						$displayHTML .= "No " . $expressionType->shortName . " terms are defined.<br /><br />";
					}else{

						//loop through each expression for this resource / expression type combination
						foreach ($expressionArray as $expression){
							//get qualifiers into an array
							$qualifierArray = array();
							foreach ($expression->getQualifiers as $qualifier){
								$qualifierArray[] = $qualifier->shortName;
								if (strtoupper($qualifier->shortName) == strtoupper($permittedQualifier)){
									$qualifierImage = "<img src='images/icon_check.gif'>";
								}else if (strtoupper($qualifier->shortName) == strtoupper($prohibitedQualifier)){
									$qualifierImage = "<img src='images/icon_x.gif'>";
								}else{
									$qualifierImage = "";
								}
							}


							//determine document effective date
							$document = new Document(new NamedArguments(array('primaryKey' => $expression->documentID)));

							if ((!$document->effectiveDate) || ($document->effectiveDate == '0000-00-00')){
								$effectiveDate = format_date($document->getLastSignatureDate());
							}else{
								$effectiveDate = format_date($document->effectiveDate);;
							}

							$displayHTML .= "Terms as of " . format_date($expression->getLastUpdateDate) . ".  ";

							$displayHTML .= "The following terms apply ONLY to articles accessed via <a href='" . $targetArray['target_url'] . "' target='_blank'>" . $targetArray['public_name'] . "</a><br /><br />";

							$displayHTML .= "<div style='margin:0 0 30px 20px;'>";

							$displayHTML .= "<div class='shaded' style='width:630px; padding:3px;'>";
							$displayHTML .= "<b>" . $expressionType->shortName . " Notes:</b>&nbsp;&nbsp;" . $qualifierImage;

							//start bulletted list
							$displayHTML .= "<ul>\n";

							//first in the bulleted list will be the list of qualifiers, if applicable
							if (count($qualifierArray) > 0){
								$displayHTML .= "<li>Qualifier: " . implode(",", $qualifierArray) . "</li>\n";
							}

							foreach ($expression->getExpressionNotes as $expressionNote){
								$displayHTML .= "<li>" . $expressionNote->note . "</li>\n";
							}

							$displayHTML .= "</ul>\n";
							$displayHTML .= "</div>";


							//only display 'show license snippet' if there's actual license document text
							if ($expression->documentText){
								$displayHTML .= "<br />";

								$displayHTML .= "<div id='div_hide_" . $expression->expressionID . "_" . $i . "' style='width:600px;'>";
								$displayHTML .= "<a href='javascript:void(0);' class='showText smallLink' value='" . $expression->expressionID . "_" . $i . "'><img src='images/arrowright.gif'></a>&nbsp;&nbsp;<a href='javascript:void(0);' class='showText' value='" . $expression->expressionID . "_" . $i . "'>view license snippet</a>";
								$displayHTML .= "</div>";

								$displayHTML .= "<div id='div_display_" . $expression->expressionID . "_" . $i . "' style='display:none; width:600px;'>";
								$displayHTML .= "<a href='javascript:void(0);' class='hideText smallLink' value='" . $expression->expressionID . "_" . $i . "'><img src='images/arrowdown.gif'></a>&nbsp;&nbsp;<a href='javascript:void(0);' class='hideText' value='" . $expression->expressionID . "_" . $i . "'>hide license snippet</a><br />";
								$displayHTML .= "<div class='shaded' style='margin-top: 5px; padding:5px 5px 5px 18px;'>From the license agreement ($effectiveDate):<br><br><i>" . $expression->documentText . "</i></div>";
								$displayHTML .= "</div>";

							}

							$displayHTML .= "</div>";

						//end expression loop
						}

					//end expression count
					}

				//target foreach loop
				}
				$displayHTML .= "</div>";

			//expression type ID was not passed in - find out what expression types are available for these targets and prompt
			}else{
				$pageTitle = "Select Expression Type";

				$expressionTypeObj = new ExpressionType();
				$targetArray = array();
				$uniqueExpressionTypeArray = array();

				foreach ($targetsArray as $i => $targetArray){
					$expressionTypeArray = $expressionTypeObj->getExpressionTypesByResource($targetArray['public_name']);

					//loop through each displayable expression type and add to final array
					foreach ($expressionTypeArray as $expressionTypeID){
						$uniqueExpressionTypeArray[] = $expressionTypeID;
					}

				//end target loop
				}

				//make sure expression type IDs are unique
				$uniqueExpressionTypeArray = array_unique($uniqueExpressionTypeArray);

				if (count($uniqueExpressionTypeArray) == 0){
					$displayHTML = "Sorry, no available license expressions have been located in CORAL Licensing.";
				}else{
					$displayHTML .= "<div class='darkShaded' style='width:664px; padding:8px; margin:0 0 15px 0;'><span class='headerText'>Available Expression Types</span>&nbsp;&nbsp;for " . $termsToolObj->getTitle() . "</div>";


					//loop through each distinct displayable expression type
					foreach ($uniqueExpressionTypeArray as $expressionTypeID){
						$expressionType = new ExpressionType(new NamedArguments(array('primaryKey' => $expressionTypeID)));
						$displayHTML .= "&nbsp;&nbsp;<a href='?issn=" . $issn . "&isbn=" . $isbn . "&typeID=" . $expressionType->expressionTypeID . "'>" . $expressionType->shortName . "</a><br />";
					}

					$displayHTML .= "<br />";
				}

			}

		//end target count
		}

	}catch(Exception $e){
		$displayHTML = $e->getMessage() . "  Please verify your information in the configuration.ini file and try again.";
	}

//end if isbn/issn passed in
}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $pageTitle; ?></title>
<link rel="stylesheet" href="css/style.css" type="text/css" />
<script type="text/javascript" src="js/plugins/jquery.js"></script>
<script type="text/javascript" src="js/index.js"></script>
</head>
<body>
<center>
<table style='text-align:left;'>
	<tr>
		<td style='vertical-align:top;'>

		<table style='background: white; padding:10px;'>
			<tr>
				<td><?php echo $displayHTML; ?></td>
			</tr>
		</table>
		</td>
	</tr>
</table>

<br />

</center>
</body>
</html>















