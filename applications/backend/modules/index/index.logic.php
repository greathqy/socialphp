<?php
/**
 * @file	后台index模块逻辑
 * @author	greathqy@gmail.com
 */
class indexLogic Extends Logic
{
	/**
	 * 获得所有单独的schema名字
	 *
	 * @return Array
	 */
	public function getAllStandaloneSchemas() {
		$config = Configurator::$generalConf['manifest'];
		$ret = array();

		foreach ($config as $schemaName => $appModule) {
			$schemaInfo = s7::getSchema($schemaName);
			$schemaInfo = $schemaInfo['struct'];

			$ret[] = array(
				'schema' => $schemaName,
				'desc' => $schemaInfo['desc'],
				'type' => $schemaInfo['type'],
				'storage' => $schemaInfo['storage'],
				);
		}

		return $config;
	}
}
