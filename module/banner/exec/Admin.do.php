<?php
REQUIRE_ONCE '../../../config/default.conf.php';

$mDB = &DB::instance();
$mMember = &Member::instance();
$mBanner = new ModuleBanner();
$member = $mMember->GetMemberInfo();

$action = Request('action');
$do = Request('do');

$return = array();
$errors = array();


if ($action == 'section') {
	if ($do == 'add' || $do == 'modify') {
		$insert = array();
		$insert['title'] = Request('title');
		$insert['type'] = Request('type');
		$insert['point'] = Request('point');
		
		$fileType = array();
		if (Request('IMG') == 'on') $fileType[] = 'IMG';
		if (Request('SWF') == 'on') $fileType[] = 'SWF';
		if (Request('TEXT') == 'on') $fileType[] = 'TEXT';
		$insert['filetype'] = implode(',',$fileType);
		
		$insert['width'] = Request('width');
		$insert['height'] = Request('height');
		$insert['allow_user'] = Request('allow_user') == 'on' ? 'TRUE' : 'FALSE';
		$insert['auto_active'] = Request('auto_active') == 'on' ? 'TRUE' : 'FALSE';
		$insert['limit'] = Request('limit');
		
		if ($do == 'add') {
			$insert['code'] = Request('code');
			if ($mDB->DBcount($mBanner->table['section'],"where `code`='{$insert['code']}'") > 0) {
				$errors['code'] = '광고코드가 중복됩니다.';
			}
			
			if (sizeof($errors) == 0) {
				$mDB->DBinsert($mBanner->table['section'],$insert);
				$return['success'] = true;
			} else {
				$return['success'] = false;
				$return['errors'] = $errors;
			}
		} else {
			$code = Request('code');
			$mDB->DBupdate($mBanner->table['section'],$insert,'',"where `code`='$code'");
			$return['success'] = true;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$code = explode(',',Request('code'));
		
		for ($i=0, $loop=sizeof($code);$i<$loop;$i++) {
			$mDB->DBupdate($mBanner->table['section'],"where `code`='{$code[$i]}'");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}

if ($action == 'item') {
	if ($do == 'add' || $do == 'modify') {
		$insert = array();
		$insert['mno'] = Request('mno');
		$insert['type'] = Request('type');
		$insert['point'] = Request('point');
		$insert['paid_point'] = Request('paid_point');
		$insert['start_date'] = Request('start_date') != null ? Request('start_date') : '0000-00-00';
		$insert['end_date'] = Request('end_date') != null ? Request('end_date') : '0000-00-00';
		$insert['bannertext'] = Request('bannertext');
		$insert['url'] = Request('url');
		
		if ($do == 'add') {
			$code = Request('code');
			$section = $mDB->DBfetch($mBanner->table['section'],array('filetype','width','height'),"where `code`='$code'");
		} else {
			$idx = Request('idx');
			$item = $mDB->DBfetch($mBanner->table['item'],'*',"where `idx`='$idx'");
			$section = $mDB->DBfetch($mBanner->table['section'],array('filetype','width','height'),"where `code`='{$item['code']}'");
		}
		
		if (isset($_FILES['bannerfile']['tmp_name']) == true && $_FILES['bannerfile']['tmp_name']) {
			$filetype = getimagesize($_FILES['bannerfile']['tmp_name']);
			if ($filetype[2] == 4 && in_array('SWF',explode(',',$section['filetype'])) == false) {
				$errors['bannerfile'] = '선택한 배너영역에는 플래시파일(SWF)형식을 지원하지 않습니다.';
			}
			
			if (in_array($filetype[2],array(1,2,3)) == true && in_array('IMG',explode(',',$section['filetype'])) == false) {
				$errors['bannerfile'] = '선택한 배너영역에는 이미지파일(GIF,JPG,PNG)형식을 지원하지 않습니다.';
			}
			
			if (in_array($filetype[2],array(1,2,3,4,13)) == false) {
				$errors['bannerfile'] = '배너파일은 이미지파일(GIF,JPG,PNG)또는 플래시파일(SWF)파일만 허용됩니다.';
			}
			
			if ($filetype[0] != $section['width'] && $filetype[1] != $section['height']) {
				$errors['bannerfile'] = '배너파일의 크기는 가로 '.$section['width'].'픽셀, 세로 '.$section['height'].'픽셀만 가능합니다.';
			}
		}
		
		if (sizeof($errors) == 0) {
			if (isset($_FILES['bannerfile']['tmp_name']) == true && $_FILES['bannerfile']['tmp_name']) {
				$filetype = getimagesize($_FILES['bannerfile']['tmp_name']);
				$insert['bannertype'] = $filetype[2] == '4' ? 'SWF' : 'IMG';
			} else {
				$insert['bannertype'] = 'TEXT';
			}
			
			if ($do == 'add') {
				$insert['code'] = Request('code');
				$insert['is_active'] = 'TRUE';

				if ((in_array('IMG',explode(',',$section['filetype'])) == true || in_array('SWF',explode(',',$section['filetype'])) == true) && in_array('TEXT',explode(',',$section['filetype'])) == true) {
					if ((isset($_FILES['bannerfile']['tmp_name']) == false || !$_FILES['bannerfile']['tmp_name']) && $insert['bannertext'] == '') {
						$errors['bannerfile'] = '배너파일을 첨부하거나, 홍보문구를 입력하셔야 합니다.';
					}
				} else if (in_array('IMG',explode(',',$section['filetype'])) == true || in_array('SWF',explode(',',$section['filetype'])) == true) {
					if (isset($_FILES['bannerfile']['tmp_name']) == false || !$_FILES['bannerfile']['tmp_name']) {
						$errors['bannerfile'] = '배너파일을 반드시 첨부하셔야 합니다.';
					}
				} else if ($section['filetype'] == 'TEXT') {
					if (!$insert['bannertext']) {
						$errors['bannerfile'] = '홍보문구를 반드시 입력하셔야 합니다.';
					}
				}

				if (isset($_FILES['bannerfile']['tmp_name']) == true && $_FILES['bannerfile']['tmp_name']) {
					$filename = md5_file($_FILES['bannerfile']['tmp_name']).'.'.time().'.'.rand(100000,999999).'.'.GetFileExec($_FILES['bannerfile']['name']);
					if (CreateDirectory($_ENV['userfilePath'].$mBanner->userfile.'/item') == true) {
						move_uploaded_file($_FILES['bannerfile']['tmp_name'],$_ENV['userfilePath'].$mBanner->userfile.'/item/'.$filename);
						$insert['bannerpath'] = '/item/'.$filename;
					}
				}
				$mDB->DBinsert($mBanner->table['item'],$insert);
			} else {
				if (isset($_FILES['bannerfile']['tmp_name']) == true && $_FILES['bannerfile']['tmp_name']) {
					@unlink($_ENV['userfilePath'].$mBanner->userfile.$item['bannerpath']);
					
					$filename = md5_file($_FILES['bannerfile']['tmp_name']).'.'.time().'.'.rand(100000,999999).'.'.GetFileExec($_FILES['bannerfile']['name']);
					if (CreateDirectory($_ENV['userfilePath'].$mBanner->userfile.'/item') == true) {
						move_uploaded_file($_FILES['bannerfile']['tmp_name'],$_ENV['userfilePath'].$mBanner->userfile.'/item/'.$filename);
						$insert['bannerpath'] = '/item/'.$filename;
					}
				}
				
				$mDB->DBupdate($mBanner->table['item'],$insert,'',"where `idx`='$idx'");
			}
			$return['success'] = true;
		} else {
			$return['success'] = false;
			$return['errors'] = $errors;
		}
		
		exit(json_encode($return));
	}
	
	if ($do == 'activemode') {
		$idx = Request('idx');
		$value = Request('value');
		
		if ($value == 'TRUE') {
			$reset = Request('reset') ? Request('reset') : 'FALSE';
			
			if ($reset == 'TRUE') {
				$idx = explode(',',$idx);
				for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
					$item = $mDB->DBfetch($mBanner->table['item'],array('type','start_date'),"where `idx`='{$idx[$i]}'");
					if ($item['type'] == 'CPM' && $item['start_date'] < date('Y-m-d')) {
						$start_date = date('Y-m-d');
						$end_date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+30,date('Y')));
						$mDB->DBupdate($mBanner->table['item'],array('is_active'=>'TRUE','start_date'=>$start_date,'end_date'=>$end_date),'',"where `idx`='{$idx[$i]}'");
					} else {
						$mDB->DBupdate($mBanner->table['item'],array('is_active'=>'TRUE'),'',"where `idx`='{$idx[$i]}'");
					}
				}
			} else {
				$mDB->DBupdate($mBanner->table['item'],array('is_active'=>'TRUE'),'',"where `idx` IN ($idx)");
			}
		} else {
			$mDB->DBupdate($mBanner->table['item'],array('is_active'=>'FALSE'),'',"where `idx` IN ($idx)");
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
	
	if ($do == 'delete') {
		$idx = explode(',',Request('idx'));
		for ($i=0, $loop=sizeof($idx);$i<$loop;$i++) {
			$data = $mDB->DBfetch($mBanner->table['item'],'*',"where `idx`='{$idx[$i]}'");
			$mDB->DBdelete($mBanner->table['item'],"where `idx`='{$idx[$i]}'");
			$mDB->DBstatus($mBanner->table['log_count'],"where `bno`='{$idx[$i]}'");
			$mDB->DBstatus($mBanner->table['log_click'],"where `bno`='{$idx[$i]}'");
			
			if ($item['bannerpath'] && file_exists($_ENV['userfilePath'].$mBanner->userfile.$item['bannerpath']) == true) {
				unlink($_ENV['userfilePath'].$mBanner->userfile.$item['bannerpath']);
			}
		}
		
		$return['success'] = true;
		exit(json_encode($return));
	}
}
?>