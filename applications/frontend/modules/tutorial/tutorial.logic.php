<?php
/**
 * @file 新手导航模块逻辑
 * @author greathqy@gmail.com
 */
class tutorialLogic extends Logic
{
	/**
	 * 用户是否需要进行新手导航
	 *
	 * @param Integer	$uid	用户id
	 * @return Boolean
	 */
	public function isUserNeedApplyTutorial($uid) {
		$userTutorialStatus = s7::get('new_user_tutorial', $uid);
		$maxSteps = Configurator::get('module.frontend_tutorial.module.max_steps');

		$nextStep = 1;
		if ($userTutorialStatus) {
			if ($userTutorialStatus != $maxSteps) {
				$nextStep = (int) $userTutorialStatus + 1;
			} else {
				$nextStep = $maxSteps + 1;
			}
		}
		
		if ($nextStep > $maxSteps) {
			return FALSE;
		}

		return $nextStep;
	}
}
