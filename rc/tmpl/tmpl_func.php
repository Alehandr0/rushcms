<?php

	function GetCurrentYear(){
		
		return date("Y");
		
	}
	
	function SpanWrapCurrentPageLinks($HTML, $Wrap){
		
		global $PageDataSQL;
		
		$Pattern = '/<a href="'.str_replace('/', '\/', $PageDataSQL['p_url']).'"([\s\S]*)>([\s\S]+)<\/a>/iU';

		return preg_replace($Pattern, $Wrap, $HTML);
		
	}
	
	function GetFromJSONData($Options){
		
		static $DataArray;
		
		global $PageDataSQL;
		
		$Result = '';
		
		if($Options){
			
			$Params = array_map('trim', explode(',', $Options));
			
			$ColumnName = $Params[0];
			$DataKey = $Params[1];
			$DefaultValParam = $Params[2];

			if(trim($PageDataSQL[$ColumnName])){
				
				if(!$DataArray || !is_array($DataArray)) $DataArray = json_decode($PageDataSQL[$ColumnName], true);

				$Result = $DataArray[$DataKey];
				
			}
			
			if(!$Result && $DefaultValParam){
			
				$DefaultData = explode(':', $DefaultValParam);
				
				if($DefaultData[0]=='value') $Result = $DefaultData[1];
				else if($DefaultData[0]=='field') $Result = $PageDataSQL[$DefaultData[1]];
				else $Result = '';
				
			}
			
		}

		return htmlspecialchars(trim($Result));
		
	}	
	
	function GetBlogArticlesList($HTML, $Limit){
		
		global $MySQLConn;
		
		if($Limit) $Limit = "LIMIT 0, $Limit";
		
		$result = $MySQLConn->query("SELECT p_url, tree_name, tree_cd, data_content FROM col_pages WHERE tree_parent_id='12' AND tree_vis='1' ORDER BY tree_cd DESC $Limit");
		while($row = $result->fetch_assoc()){
			
			$row['RUS_DATE'] = date("d.m.Y", strtotime($row['tree_cd']));
			$row['SHORT_TEXT'] = trim(mb_substr(strip_tags($row['data_content']), 0, 250)).'...';
			$row['TITLE_NAME'] = htmlspecialchars($row['tree_name']);
			
			if(!is_array($SearchTags)){

				$SearchTags = array_keys($row);
				array_walk($SearchTags, function(&$Item) { $Item = '{{'.$Item.'}}'; });
				
			}
			
			$ResultContent .= str_ireplace($SearchTags, array_values($row), $HTML);
			
		}
		mysqli_free_result($result);

		return $ResultContent;		
		
	}
	
	function GetThisPageBlogArticle($HTML){

		global $PageDataSQL;
	
		$row = $PageDataSQL;
		
		$row['RUS_DATE'] = date("d.m.Y", strtotime($row['tree_cd']));
		$row['SHORT_TEXT'] = trim(mb_substr(strip_tags($row['data_content']), 0, 250)).'...';

		$SearchTags = array_keys($row);
		array_walk($SearchTags, function(&$Item) { $Item = '{{'.$Item.'}}'; });
		
		$ResultContent = str_ireplace($SearchTags, array_values($row), $HTML);

		return $ResultContent;				
		
	}
	
	function GetItemsList($HTML, $Options){
		
		global $MySQLConn, $PageDataSQL;
		
		$Options = explode(',', $Options);

		$Limit = intval($Options[0]);
		if($Limit>0) $Limit = "LIMIT 0, $Limit";
		else $Limit = '';
		
		$Order = $Options[1];
		
		$result = $MySQLConn->query("SELECT id, p_url, tree_name, data_img, data_price FROM col_pages WHERE tree_parent_id='13' AND tree_vis='1' ORDER BY $Order $Limit");
		while($row = $result->fetch_assoc()){

			$row['TITLE_NAME'] = htmlspecialchars($row['tree_name']);
			$row['FORMATTED_COST'] = number_format($row['data_price'], 0, '.', ' ');
			
			if(!$row['data_img']) $row['IMG'] = '/rc/pic/no_photo.jpg';
			else{
				
				$ImagesArray = json_decode($row['data_img'], true);

				if(is_array($ImagesArray)) $row['IMG'] = "/rc/upload/col_pages/$row[id]/".$ImagesArray[0][0]['img'];
				
			}
			
			if(!is_array($SearchTags)){

				$SearchTags = array_keys($row);
				array_walk($SearchTags, function(&$Item) { $Item = '{{'.$Item.'}}'; });
				
			}
			
			$ResultContent .= str_ireplace($SearchTags, array_values($row), $HTML);
			
		}
		mysqli_free_result($result);		
		
		return $ResultContent;	
		
	}	
	function GetThisPageItem($HTML){
		
		global $PageDataSQL;
		
		$row = $PageDataSQL;

		$row['TITLE_NAME'] = htmlspecialchars($row['tree_name']);
		$row['FORMATTED_COST'] = number_format($row['data_price'], 0, '.', ' ');
		
		if(!$row['data_img']) $row['IMG'] = '/rc/pic/no_photo.jpg';
		else{
			
			$ImagesArray = json_decode($row['data_img'], true);
			
			if(is_array($ImagesArray)) $row['IMG'] = "/rc/upload/col_pages/$row[id]/".$ImagesArray[0][0]['img'];
			
		}

		$SearchTags = array_keys($row);
		array_walk($SearchTags, function(&$Item) { $Item = '{{'.$Item.'}}'; });

		$ResultContent .= str_ireplace($SearchTags, array_values($row), $HTML);
		
		return $ResultContent;	
		
	}
	
	function GetCartItemsList($HTML){
		
		global $MySQLConn;
		
		$CartCookie = $_COOKIE['cart'];
		
		if($CartCookie){
			
			$CartArray = json_decode($CartCookie, true);
			
			if(is_array($CartArray)){

				foreach($CartArray as $Id => $Val){
					
					if($SQLWhere) $SQLWhere .= "OR (id='$Id' AND tree_vis='1')";
					else $SQLWhere .= "(id='$Id' AND tree_vis='1')";
					
				}
				
				if($SQLWhere){

					$result = $MySQLConn->query("SELECT id, tree_name, data_price FROM col_pages WHERE $SQLWhere");
					while($row = $result->fetch_assoc()){
						
						$row['FORMATTED_COST'] = number_format($row['data_price'], 0, '.', ' ');
						
						if(!is_array($SearchTags)){

							$SearchTags = array_keys($row);
							array_walk($SearchTags, function(&$Item) { $Item = '{{'.$Item.'}}'; });
							
						}
						
						$ResultContent .= str_ireplace($SearchTags, array_values($row), $HTML);
						
					}
					mysqli_free_result($result);

				}

			}
			
		}
		
		return $ResultContent;
		
	}
	
	function GetCartCount(){
		
		$Count = '';
		
		$CartCookie = $_COOKIE['cart'];
		
		if($CartCookie){
			
			$CartArray = json_decode($CartCookie, true);
			
			if(is_array($CartArray) && !empty($CartArray)){

				$Count = '('.count($CartArray).')';

			}
			
		}
		
		return $Count;
		
	}
	
	
	function GetTestData(){
		
		global $PageDataSQL;
		
		$PageDataSQL['data_content'] = trim(preg_replace('/\s+/', ' ', htmlspecialchars($PageDataSQL['data_content'])));
		
		return var_export($PageDataSQL, true);
		
	}	
	
?>