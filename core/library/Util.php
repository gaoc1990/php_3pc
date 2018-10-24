<?php 
namespace tpc\common;

/**
 * Util
 */
class Util
{
	/**
	 * 生成事务组唯一标识
	 * @return [type] [description]
	 */
	public static function createXid(){
		$xid = "xid_" . uniqid() ."_" . rand(10000,99999);
		return $xid;
	}

	public static function createTaskKey() {
        return "taskkey_" . uniqid() . "_" . rand(10000,99999);
    }

	/**
	 * 生成事务发起者唯一标识
	 * @return [type] [description]
	 */
	public static function createStarterId($xid){
		$starterId = $xid . '_' . 'starter' . '_' .  uniqid();
		return $starterId;
	}

	/**
	 * 生成事务参与者唯一标识
	 * @param  [type] $xid [description]
	 * @return [type]      [description]
	 */
	public static function createActorId($xid){
		$actorId = $xid . '_' . 'actor' . '_' . uniqid();
		return $actorId;
	}

	/**
	 * curl的post
	 * 
	 * @return [type] [description]
	 */
	public static function post($url, $data, $header=array()){
		$ch = curl_init ();
		
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		if($header){
			curl_setopt ( $ch, CURLOPT_HEADER, $header );
		}
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
		$return = curl_exec ( $ch );
		curl_close ( $ch );

		$errno = curl_errno($ch);
		if($errno){
			return false;
		}

		return $return;
	}

} 