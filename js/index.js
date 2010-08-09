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


$(document).ready(function(){


	$('.showText').click(function() {
		$('#div_display_' + $(this).attr('value')).show();
		$('#div_hide_' + $(this).attr('value')).hide();
	 });
      


	$('.hideText').click(function() {
		$('#div_display_' + $(this).attr('value')).hide();
		$('#div_hide_' + $(this).attr('value')).show();
	 });      
                   
});
 



