<?php

namespace Drutiny\Plugin\Drupal7\Audit;

use Drutiny\Audit;
use Drutiny\Sandbox\Sandbox;
use Drutiny\RemediableInterface;
use Drutiny\Annotation\Param;

/**
 * Generic module is disabled check.
 * @Param(
 *  name = "module",
 *  description = "The name of the module to check is disabled.",
 *  type = "string",
 * )
 */
class ModuleDisabled extends Audit implements RemediableInterface {

  /**
   * @inheritdoc
   */
  public function audit(Sandbox $sandbox) {

    $module = $sandbox->getParameter('module');

    try {
      $info = $sandbox->drush(['format' => 'json'])->pmList();
    }
    catch (DrushFormatException $e) {
      return strpos($e->getOutput(), $module . ' was not found.') !== FALSE;
    }

    if (!isset($info[$module])) {
      return TRUE;
    }

    $status = strtolower($info[$module]['status']);

    return ($status == 'not installed');
  }

  /**
   * @inheritdoc
   */
  public function remediate(Sandbox $sandbox) {
    $module = $sandbox->getParameter('module');
    $sandbox->drush()->dis($module, '-y');
    return $this->audit($sandbox);
  }

}
