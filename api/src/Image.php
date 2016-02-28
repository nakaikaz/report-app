<?php
//namespace lib\util;

class Image {

	/**
	 * 元画像を第３パラメータのサイズでコピーする。
	 * コピーに失敗した時は、falseを返す。
	 *
	 * @param string $src_file
	 * @param string $dst_file
	 * @param array $size
	 * @return bool
	 *
	 */
	public static function copy($src_file, $dst_file, $size){
		if(empty($src_file) || empty($dst_file)){
			throw new Exception('args must be filled.');
		}
		if(is_array($size)){
			$dst_w = $size['width'];
			$dst_h = $size['height'];
		}
		$image_info = getimagesize($src_file);
		$src_w = $image_info[0];
		$src_h = $image_info[1];
		$image_type = $image_info['mime'];

		switch($image_type){
		case 'image/png':
			$src_res = imagecreatefrompng($src_file);
			break;
		case 'image/gif':
			$src_res = imagecreatefromgif($src_file);
			break;
		case 'image/jpeg':
		case 'image/pjpeg':
			$src_res = imagecreatefromjpeg($src_file);
			break;
		default:
			throw new Exception('image type is not supported');
			break;
		}

		$dst_res = imagecreatetruecolor($dst_w, $dst_h);
		$src_x = $src_y = 0;
		if($src_w > $src_h){
			$src_x = ($src_w - $src_h) / 2;
			$src_w = $src_h;
		}
		if($src_h > $src_w){
			$src_y = ($src_h - $src_w) / 2;
			$src_h = $src_w;
		}
		imagecopyresampled($dst_res, $src_res, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

		switch($image_type){
		case 'image/png':
			$ret = imagepng($dst_res, $dst_file);
			break;
		case 'image/gif':
			$ret = imagegif($dst_res, $dst_file);
			break;
		case 'image/jpeg':
		case 'image/pjpeg':
			$ret = imagejpeg($dst_res, $dst_file);
			break;
		default:
			throw new Exception('image type is not supported');
			break;
		}

		imagedestroy($src_res);
		imagedestroy($dst_res);

		return $ret;
	}
}
