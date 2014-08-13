<?php
/**
* 2014 Affinity-Engine
*
* NOTICE OF LICENSE
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade AffinityItems to newer
* versions in the future. If you wish to customize AffinityItems for your
* needs please refer to http://www.affinity-engine.fr for more information.
*
*  @author    Affinity-Engine SARL <contact@affinity-engine.fr>
*  @copyright 2014 Affinity-Engine SARL
*  @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GPL Version 2 (GPLv2)
*  International Registered Trademark & Property of Affinity Engine SARL
*/

final class AENotification {

	private $notificationList;

	private $repository;
	
	public function __construct($pnotificationList) {
		$this->notificationList = $pnotificationList;
		$this->repository = new NotificationRepository();
	}

	public function syncNewElement() {
		$request = new NotificationRequest($this->getNotificationIds());
		if($request->post()) {
			$this->repository->insert($this->notificationList);
		}
	}

	public function syncUpdateElement() {
		$this->repository->update($this->notificationList);
	}

	public function syncDeleteElement() { }

	public function getNotificationIds() {
		$notificationIds = array();
		foreach ($this->notificationList as $notification) {
			array_push($notificationIds, $notification->id);
		}
		return $notificationIds;
	}

	public static function convert($notices) {
		$notifications = array();
		foreach ($notices as $notice) {
			$notification = new stdClass();
			$translations = array();
			$notification->id = $notice->publicId;
			$notification->date = $notice->date;
			foreach ($notice->translations as $value) {
				$translation = new stdClass();
				$translation->language = $value->language;
				$translation->title = $value->title;
				$translation->text = $value->text;
				$translation->notificationId = $notice->publicId;
				array_push($translations, $translation);
			}
			$notification->translations = $translations;
			array_push($notifications, $notification);
		}
		return $notifications;
	}


}

?>