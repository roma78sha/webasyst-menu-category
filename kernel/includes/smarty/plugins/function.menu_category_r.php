<?php
/*
 * Smarty plugin
 * --------------------------------------------------------------------
 * Type:     function
 * Name:     menu_category_r
 * Purpose:  menu category module (модуль для webasyst, меню категорий)
 *
 * @version				 2.1 beta, free, как есть
 * @author	    Roman Shulyak © декабрь 2014г   roma78sha@gmail.com
 * --------------------------------------------------------------------
 */
 
function smarty_function_menu_category_r ($params, &$smarty){
	$settings = array();
	extract($params);
	// if(!$params['start'] && !$params['link']) return;// $t = catGetCategoryCompactCList(1);// $t = catGetCategoryCList();
	
	// $start начинаем с этого уровня вложенности
	$start = $start?$start:0;
	
	// $hide исключаем эти категории по id (558 555 или 558,555)
	$hide = $hide?$hide:"";

	$result = mysql_query("SELECT * FROM SC_categories ORDER BY sort_order ASC");
	$cats = getBuildCats($result);

	$stop = count($cats[1]["children"]);
	$ret = "<ul>".getBuildTree($cats, $start, $stop, $hide)."</ul>";

	$smarty->assign('menu_category_result',$ret);

	$smarty->display (DIR_FTPLS.'/menu_category_r.html');
}
function getBuildCats($result){
   
	$levels = array ();
    $tree = array ();
    $cur = array ();

	while($rows = mysql_fetch_assoc ($result)){
		
		$cur = &$levels[$rows["categoryID"]];
        $cur['parent'] = $rows['parent'];
        $cur["name_en"] = $rows["name_en"];
        $cur["name_ru"] = $rows["name_ru"];
        $cur["categoryID"] = $rows["categoryID"];
		
		// ЧПУ
		if(!MOD_REWRITE_SUPPORT){
		  $cur["href"] = $rows["slug"]?"?categoryID=".$rows["categoryID"]."&category_slug=".$rows["slug"]:"?categoryID=".$rows["categoryID"]; 
		}else{
		  $cur["href"] = "category/".$rows["slug"];
		}

		// собираем
        if($rows['parent'] == 0){
			$tree[$rows["categoryID"]] = &$cur;
        }
        else{
			$levels[$rows['parent']]['children'][$rows["categoryID"]] = &$cur;
        }
       
    }
    return $tree;
   
}

function getBuildTree($arr, $start = 0, $stop = 2, $hide = "", $deep = -1, $cid = 0, $ii = 0){
	$deep++;
	$firstlast = "";
	
    $i = 0;
	$max = count($arr);
	foreach($arr as $k=>$v){
	  $i++;
	  $cid = $v["categoryID"];
	  if($i == 1) {
		$firstlast = "first ";
	  } else if($i == $max && $max != 1) {
		$firstlast = "last ";
	  }
		
	  // пропустить
	  if(strripos($hide, $cid) === false){

        if($deep > $start){
		  $out .= '<li class="'.$firstlast.'menu-category-li menu-category-id'.$cid.' menu-category-deep'.$deep.'"><a href="/'.$v["href"].'"><span class="menu-category-open" data-id="'.$cid.'"></span><span class="menu-category-name">'.$v["name_ru"].'</span></a>';
	$firstlast = "";
		}
        if(!empty ($v['children'])){
			
			
			if($deep > $start){
			  $out .= '<ul class="menu-category-ul menu-category-id'.$cid.' menu-category-deep'.$deep.'">';
			}

				$out .= getBuildTree($v['children'], $start, $stop, $hide, $deep, $cid, $ii);
			  
			if($deep > $start){
			  $out .= '</ul>';
			}
        }
		
		if($deep > $start){
		  $out .= '</li>';
		}
		
	  } else {}

    }
    return $out;
   
}
?>
