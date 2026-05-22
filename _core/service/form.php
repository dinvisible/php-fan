<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Parsing form service
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.007 (31.08.2015)
 */
class form extends \fan\core\base\service\multi
{
    /**
     * Regexp for parse combi field name
     */
    public const RE_COMBI = '/([^\[]+)?\[([^\]]+)\]/';

    private static array $instances = [];

    /**
     * @var \fan\core\block\form\parser
     */
    protected ?object $block = null;

    /**
     * @var \fan\core\base\meta\row
     */
    protected ?object $formMeta = null;
    /**
     * Field description from meta data
     * @var array
     */
    protected array $fieldMeta = [];
    /**
     * Field types
     * @var array
     */
    protected array $fieldTypes = [];

    /**
     * Form's data from HTTP request
     * @var array
     */
    protected array $fieldValue = [];

    /**
     * Form's data from Form parts
     * @var array
     */
    protected array $partFieldValue = [];

    /**
     * Form's data for make field (select/radio/checkbox)
     * @var array
     */
    protected array $fieldData = [];
    /**
     * Form error
     * @var array
     */
    protected array $errorMsg = [];
    /**
     * Form error
     * @var boolean
     */
    protected bool $isError = false;

    /**
     * Form Validators
     * @var array
     */
    protected array $validators = [];

    /**
     * Form Validators
     * @var array
     */
    protected array $validatorClasses = [];

    /**
     * Role name form
     * @var string
     */
    protected string $roleName = '';

    protected function __construct(\fan\core\block\form\parser $block)
    {
        parent::__construct(empty(self::$instances));

        $this->block     = $block;
        $this->formMeta  = $block->getFormMeta();
        if (empty($this->formMeta)) {
            throw new fatalException($this, 'Form meta isn\'t set for block "' . get_class($block) . '".');;
        }
        $this->fieldMeta = $this->formMeta->get('fields');
        if (empty($this->fieldMeta)) {
            throw new fatalException($this, 'Form fields meta aren\'t set for block "' . get_class($block) . '".');;
        }

        $activeElements = $this->getConfig('ACTIVE_ELEMENTS', ['input', 'checking', 'select', 'select_separated', 'select_multi', 'select_multi_separated']);
        foreach ($activeElements as $v) {
            foreach ($this->_getFormMeta(['design', $v], []) as $k => $tmp) {
                $this->fieldTypes[$k] = $v;
            }
        }

        foreach ($this->config['VALIDATORS'] as $k => $v0) {
            foreach ($v0 as $v1) {
                $this->validatorClasses[$v1] = $k;
            }
        }

        $this->_presetFieldValue();

        self::$instances[$block->getBlockName()] = $this;
    }

    // ======== Static methods ======== \\
    public static function instance(\fan\core\block\form\parser $block): self
    {
        $name = $block->getBlockName();
        if (!isset(self::$instances[$name])) {
            new self($block);
        }
        return self::$instances[$name];
    }

    // ======== Main Interface methods ======== \\

    /**
     * Transforms form between supported representations.
     */
    public function parseForm(bool $parceEmpty = true, ?bool $parsingCondition = null, ?bool $allowTransfer = null): bool
    {
        // Check - is need to validate this form
        while ($this->necessaryFormParsing($parsingCondition, true)) {
            // Get data for parsing
            $this->_defineFieldValue();

            if (!$parceEmpty) {
                $isEmpty = true;
                foreach ($this->fieldValue as $v) {
                    if (!empty($v)) {
                        $isEmpty = false;
                        break;
                    }
                }
                if ($isEmpty) {
                    break;
                }
            }
            // Validate data
            if ($this->block->checkBeforeValidation()) {
                foreach ($this->fieldMeta as $fieldName => $parameters) {
                    if (!empty($parameters['not_check_by_data']) || $this->_autoCheckByData($fieldName, isset($parameters['label']) ? $parameters['label'] : $fieldName)) {
                        if (!array_key_exists($fieldName, $this->errorMsg)) {
                            $this->errorMsg[$fieldName] = null;
                        }
                        $this->_validateValueRecursive($fieldName);
                    }
                }
            }

            $this->_parseFormParts($parceEmpty);

            // Processing after validation
            if ($this->block->checkAfterValidation()) {
                $roleName = $this->block->getRoleName();

                if ($this->isError) {
                    $this->containerService('role')->killSessionRoles($roleName);
                    $this->_broadcastMessage('onError', $this->block);

                    break;
                } else {
                    $this->_broadcastMessage('onSubmit', $this->block);

                    if (!$this->isError) {
                        // Set form roles
                        if (!$this->_getFormMeta('not_role')) {
                            $this->containerService('role')->setFixQttRoles($roleName);
                        }
                        // Remove CSRF-protection code from session
                        if ((int)$this->_getFormMeta('csrf_protection') >= 4) {
                            $this->containerService('session',
                                $this->_getFormMeta('form_id'),
                                'form_key'
                            )->remove('csrf');
                        }

                        //ToDo: Clear cache of some blocks there
                        //\fan\project\service\cache::instance()->clear($this->formMeta->get(['cache', 'clear']));

                        $this->_onSubmitTransfer(
                                $allowTransfer,
                                'commit',
                                strtoupper((string)$this->_getFormMeta('action_method', 'POST')) !== 'GET'
                        );
                    }
                    break;
                }
            }
            break;
        }
        return !$this->isError;
    }

    public function necessaryFormParsing(mixed $parsingCondition = null, bool $chkButton = true): bool
    {
        if (!is_null($parsingCondition)) {
            return $parsingCondition;
        }
        if ($this->_getFormMeta('always_parse')) {
            return true;
        }

        $request     = $this->containerService('request');
        $requestType = $this->_getFormMeta('request_type', 'GP');

        // Analyse key field
        $srcKeyVal = $this->_getFormMeta('form_id');
        if (!empty($srcKeyVal)) {
            if ((int)$this->_getFormMeta('csrf_protection') >= 4) {
                $csrfCode   = $this->containerService('session', $srcKeyVal, 'form_key')->get('csrf');
                if (empty($csrfCode)) {
                    $this->isError = true;
                    // ToDo: error message for user
                    return false;
                }
                $srcKeyVal .= '_' . $csrfCode;
            }
            $keyField = $request->get('form_key_field', $requestType);
            if ((string)$srcKeyVal !== (string)$keyField) {
                return false;
            }
        }


        // Analyse submit buttons
        $submit = $this->_getFormMeta('form_submit_name');
        if ($submit) {
            if (is_array($submit)) {
                foreach ($submit as $v) {
                    if ($request->get($v, $requestType)) {
                        return true;
                    }
                }
            } elseif ($request->get($submit, $requestType)) {
                return true;
            }
        }

        if ($chkButton) {
            // Analyse exception buttons
            $exceptions = $this->_getFormMeta('form_exceptions');
            if ($keyField && $exceptions) {
                foreach ($exceptions as $v) {
                    if ($request->get($v, $requestType)) {
                        return false;
                    }
                }
            } elseif ($submit) {
                return false;
            }
        }

        // If doesn't set Key field, Submit button and Exception button - parse if $_POST doesn't empty
        return !empty($keyField) || !empty($_POST);
    }

    public function strForJsValidation(): ?string
    {
        $str = '';
        $reqMsg = $this->_getFormMeta($this->isMultiLanguage() ? 'required_msg' : 'required_msg_alt');
        foreach ($this->fieldMeta->toArray() as $fieldName => $parameters) {
            $rules = '';
            if (!empty($parameters['is_required'])) {
                $rules .= '{rule_name:\'isRequired\', ';
                $rules .= 'error_msg:\'' . $this->reduceMessage($reqMsg, $parameters['label']) . '\'}';
            }

            if (isset($parameters['validate_rules'])) {
                foreach ($parameters['validate_rules'] as $rule) {
                    if (empty($rule['not_js'])) {
                        $rules .= $rules ? ',' : '';

                        $rules .= '{rule_name:\'' . $rule['rule_name'] . '\', ';
                        $rules .= 'error_msg:\'' . $this->reduceMessage($rule['error_msg'], $parameters['label']) . '\'';
                        if (!empty($rule['not_empty'])) {
                            $rules .= ',not_empty:1';
                        }
                        $ruleData = '';
                        if (isset($rule['rule_data'])) {
                            foreach ($rule['rule_data'] as $key => $value) {
                                $ruleData .= $ruleData ? ',' : '';
                                $ruleData .= strtolower((string)$key) . ':';
                                if (is_bool($value)) {
                                    $ruleData .= $value ? 1 : 0;
                                } elseif (preg_match('/^(\/.+\/)([a-z]*)$/i', (string)$value, $matches)) {
                                    $ruleData .= $matches[1];
                                    if (!empty($matches[2])) {
                                        for ($i = 0; $i < strlen($matches[2]); $i++) {
                                            if (in_array($matches[2][$i], ['i', 'g', 'm'])) {
                                                $ruleData .= $matches[2][$i];
                                            }
                                        }
                                    }
                                } else {
                                    $ruleData .= '\'' . addslashes((string)$value) . '\'';
                                }
                            }
                        }
                        $rules .= $ruleData ? ',ruleData:{'.$ruleData.'}}' : '}';
                    }
                }
            }
            if ($rules) {
                $str .= $str ? ',' : '';
                $str .= '\'' . $fieldName . ($this->_isMultiVal($parameters['input_type']) ? '[]' : '') . '\':[' . $rules . ']';
            }
        }

        if ($str) {
            $jsUrl = $this->_getFormMeta('js_url', [], true);
            $root  = $this->containerService('tab')->getTabBlock('root');
            $root->setExternalJs($jsUrl['js-wrapper']);
            $root->setExternalJs($jsUrl['validator']);

            $loaderData = $this->_getFormMeta('js_loader', [], true);
            if (isset($loaderData['fields']) && isset($loaderData['url'])) {
                $jsLoader = ',loader:{url:"' . $loaderData['url'] . '",fields:["' . implode('","', $loaderData['fields']) . '"]}';
                $root->setExternalJs($jsUrl['js-loader']);
            } else {
                $jsLoader = '';
            }

            $str = 'var validation_' . $this->block->getBlockName() . '=new ' . $this->_getFormMeta('js_validator') .
                '({form:"' . $this->_getFormMeta('form_id') . '"' .
                ',err_format:"' . $this->_getFormMeta('js_err_format') . '"' .
                ($this->_getFormMeta('form_submit_name') ? ',field:"' . $this->_getFormMeta('form_submit_name') . '"' : '') .
                $jsLoader . '},{' . $str . '},_wrapper);';
        } else {
            $str = null;
        }
        return $str;
    }

    public function isMultiLanguage(): bool
    {
        $ret = $this->block->getMeta('useMultiLanguage', null);
        return is_null($ret) ? $this->containerService('locale')->isEnabled() : $ret;
    }

    public function reduceMessage(string $msg, string $label): string
    {
        $msg = preg_replace('/\<\/?(?:div|p|br).*?\>/', "\n", (string)$msg);
        $msg = preg_replace('/\<.*?\>/', '', (string)$msg);
        $msg = addslashes($this->_getErrorMesage((string)$label, (string)$msg));
        return str_replace("\n", '\n', $msg);
    }

    public function getFieldValue(mixed $fieldName = null, bool $useSubform = true): mixed
    {
        $fieldValue = $this->fieldValue;
        if ($useSubform) {
            foreach ($this->partFieldValue as $v) {
                $fieldValue = array_merge_recursive_alt($v, $fieldValue);
            }
        }
        if (empty($fieldName)) {
            return $fieldValue;
        }
        $isArray = is_array($fieldName);
        $value   = $isArray ? array_get_element($fieldValue, $fieldName, false) : array_val($fieldValue, $fieldName);
        return $value;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function setFieldValue(mixed $fieldName, mixed $value): static
    {
        if ($this->_checkName($fieldName)) {
            $this->fieldValue[$fieldName] = $value;
        }
        return $this;
    }

    /*
     * Set several field values by Array
     * @param array $values
     * @return \fan\core\service\form
     */
    public function setMassFieldValues(array $values): static
    {
        foreach ($values as $k => $v) {
            if ($this->_checkName($k)) {
                $this->fieldValue[$k] = $v;
            }
        }
        return $this;
    }

    public function getFieldData(mixed $fieldName): mixed
    {
        $key = \is_array($fieldName) ? $fieldName[0] : $fieldName;
        if (!empty($this->fieldData[$key])) {
            return $this->fieldData[$key];
        }

        $meta = $this->_getFormMeta(['fields', $key]);
        if (isset($meta->dataSource->method)) {
            $callback = [
                isset($meta->dataSource->class) ? $meta->dataSource->class : $this->block,
                $meta->dataSource->method
            ];
            $result = \call_user_func($callback, $fieldName);
        } else {
            $result = null;
        }
        if (is_null($result)) {
            $result = \adduceToArray($meta->data);
        }
        return $result;
    }

    public function setFieldData(string $fieldName, mixed $fieldData): static
    {
        $this->fieldData[$fieldName] = $fieldData;
        return $this;
    }

    public function setFieldDataByRowset(string $fieldName, \fan\core\base\model\rowset $rowset, string $textKey, ?string $valueKey = null): void
    {
        if (empty($rowset)) {
            return;
        }
        foreach ($rowset as $row) {
            $this->fieldData[$fieldName][] = [
                'value' => $valueKey ? $row->get($valueKey) : $row->getId(),
                'text'  => $row->get($textKey),
            ];
        }
    }

    public function checkDepth(mixed $val, int|float $depth): bool
    {
        if (empty($depth)) {
            return is_scalar($val);
        }
        if (!is_array($val)) {
            return false;
        }
        foreach ($val as $v) {
            if (!$this->checkDepth($v, $depth - 1)) {
                return false;
            }
        }
        return true;
    }

    public function getErrorMsg(mixed $fieldName = null): mixed
    {
        return empty($fieldName) ? $this->errorMsg : array_get_element($this->errorMsg, $fieldName, false);
    }

    public function setError(): void
    {
        $this->isError = true;
    }

    public function isError(): bool
    {
        return $this->isError;
    }

    public function checkFormRole(): bool
    {
        $roleName = $this->block->getRoleName();
        return empty($roleName) ? false : role($roleName);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    public function checkByData(mixed $value, array $data): bool
    {
        $tmp  = reset($data);
        if (!array_diff_key($tmp, ['value' => 0, 'text'  => 0])) {
            foreach ($data as $v) {
                if (is_array($value) ? in_array($v['value'], $value, true) : (string)$value === (string)$v['value']) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }


    // ======== Private/Protected methods ======== \\

    protected function _presetFieldValue(): static
    {
        foreach ($this->fieldMeta as $k => $v) {
            if (preg_match_all(self::RE_COMBI, $k, $match)) {
                $key  = array_merge([$match[1][0]], $match[2]);
                $dest =& array_get_element($this->fieldValue, $key, true);
                if (is_null($dest)) {
                    $dest = $v->get('default_value', null);
                }
            } elseif (!isset($this->fieldValue[$k])) {
                $this->fieldValue[$k] = $v->get('default_value', null);
            }
        }
        return $this;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    protected function _getFormMeta(string|array $key, mixed $default = null, bool $convToArray = false): mixed
    {
        $meta = $this->formMeta->get($key, $default);
        return $convToArray && is_object($meta) && $meta instanceof \fan\core\base\meta\row ? $meta->toArray() : $meta;
    }

    protected function _defineFieldValue(): static
    {
        $request     = $this->containerService('request');
        $requestType = $this->_getFormMeta('request_type');

        if (!$requestType) {
            $tmp = ['GET'=>'G', 'POST'=>'P', 'FILE'=>'PF'];
            $requestType = $tmp[$this->_getFormMeta('action_method', 'POST')];
            if (!$requestType) {
                $requestType = 'GPF';
            }
        }

        foreach ($this->fieldMeta as $fieldName => $parameters) {
            $depth = $match = null;
            $complex = [];
            if (preg_match_all(self::RE_COMBI, $fieldName, $match)) {
                // Complex field name, like: "foo[bar]", "foo[bar1][bar2]", etc
                $baseName = $match[1][0];
                if (empty($baseName)) {
                    throw new fatalException($this, 'Incorrect Field Name "' . $fieldName . '" - empty base-name.');
                }
                $val      = $request->get($baseName, $requestType);
                $checkVal = array_val($this->fieldValue, $match[1][0]);
                foreach ($match[2] as $v) {
                    $complex[] = $v;
                    $checkVal  = array_val($checkVal, $v);
                    if (isset($val[$v])) {
                        $val = $val[$v];
                    } else {
                        $val = null;
                        break;
                    }
                }
                if (!is_null($val) && (string)$val === (string)$checkVal) {
                    continue;
                }
            } else {
                // Simple field name, like "foo"
                $baseName = $fieldName;
                $val = $request->get($baseName, $requestType);
            }

            // Check is file
            $uploads = $this->getConfig('UPLOAD_TYPES', ['file', 'file_multiple']);
            if (in_array($parameters['input_type'], adduceToArray($uploads))) {
                $depth = empty($parameters['depth']) ? 1 : $parameters['depth'] + 1;
                if ($depth > 1 && !empty($val)) {
                    $tmp = [];
                    foreach ($val as $k => $v) {
                        if (is_array($v)) {
                            $this->_transformFileData($tmp, $k, $v);
                        }
                    }
                    $val = $tmp;
                } else {
                    $val = empty($val) || (int)$val['error'] === UPLOAD_ERR_NO_FILE ? null : $val;
                }
                $isFile = true;
            } else {
                $isFile = false;
            }

            $key  = empty($complex) ? $baseName : array_merge([$baseName], $complex);
            $dest =& array_get_element($this->fieldValue, $key, true);
            if (is_null($val) && !is_null($dest)) {
                continue;
            }
            $dest = null;

            $err  =& $this->errorMsg[$fieldName];
            $err  = null;

            if (!is_null($val)) {
                if (is_null($depth)) {
                    $depth = empty($parameters['depth']) ? 0 : $parameters['depth'];
                    if ($this->_isMultiVal($parameters['input_type'])) {
                        $depth++;
                    }
                }

                if (($depth > 0 && count($complex) === (int)$depth) || $this->checkDepth($val, $depth)) {
                    $dest = $isFile ? $val : $this->_trimDataRecursive($val, $baseName);
                } else {
                    throw new \UnexpectedValueException('Field "' . $parameters['label'] . '" of form "' . get_class_alt($this->block) . '" has incorrect depth of value.');
                }
            }
        }
        return $this;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function _trimDataRecursive(mixed $value, string $fieldName, array $index = []): mixed
    {
        if (!is_null($value)) {
            if (is_array($value)) {
                foreach ($value as $k => &$v) {
                    $v = $this->_trimDataRecursive($v, $fieldName, array_merge($index, [$k]));
                }
            } else {
                $param  = $this->_getCombiParam($fieldName, $index);
                $value  = empty($param['trim_data']) ? (string)$value : trim((string)$value);

                $maxLen = empty($param['maxlength']) ? 0 : $param['maxlength'];
                $len    = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
                if (!empty($maxLen) && $len > $maxLen) {
                    throw new \LengthException('Data has been truncated in the form "' . get_class_alt($this->block) . '" for field "' . $fieldName . '". Length was ' . $len . '.');
                }

                if (!isset($param['trim_tag']) || $param['trim_tag']) {
                    $repl = [
                        '&'  => '&amp;',
                        '"'  => '&quot;',
                        '\'' => '&#039;',
                        '<'  => '&lt;',
                        '>'  => '&gt;',
                        '\\' => '\\\\',
                    ];
                    $curRepl = (string)$this->_getFormMeta('trim_tag_val', '&"\'<>');
                    for ($i = 0; $i < strlen($curRepl); $i++) {
                        if (isset($repl[$curRepl[$i]])) {
                            $value = str_replace($curRepl[$i], $repl[$curRepl[$i]], $value);
                        }
                    }
                }
            }
        }
        return $value;
    }

    protected function _autoCheckByData(string $fieldName, string $label): bool
    {
        $data = $this->getFieldData($fieldName);
        if (!empty($data)) {
            $value = array_val($this->fieldValue, $fieldName);
            if (isset($value)) {
                if (!$this->checkByData($value, $data)) {
                    $this->isError = true;
                    throw new \UnexpectedValueException('Error in the form "' . get_class_alt($this->block) . '". Value of "' . $fieldName . '" doesn\'t correspond to source.');
                }
            }
        }
        return true;
    }

    protected function _isMultiVal(string $inpType): bool
    {
        if (!isset($this->fieldTypes[$inpType])) {
            return false;
        }
        $fieldType = $this->fieldTypes[$inpType];
        $typeMulty = adduceToArray($this->getConfig('MULTIVAL_TYPES', ['select_multi', 'select_multi_separated']));
        return in_array($fieldType, $typeMulty);
    }

    protected function _validateValueRecursive(string $fieldName, array $index = []): ?static
    {
        $match = null;
        if (preg_match_all(self::RE_COMBI, $fieldName, $match)) {
            $key = array_merge([$match[1][0]], $match[2]);
        } else {
            $key = empty($index) ? $fieldName : [$fieldName];
        }

        $value = $this->getFieldValue(empty($index) ? $key : array_merge($key, $index), false);
        $param = $this->_getCombiParam($fieldName, $index);

        $uploads = $this->getConfig('UPLOAD_TYPES', ['file', 'file_multiple']);
        if (in_array(array_val($param, 'input_type'), adduceToArray($uploads))) {
            // ToDo: Value by index
            if (!empty($value['tmp_name']) && is_array($value['tmp_name'])) {
                foreach ($value['tmp_name'] as $k => $v) {
                    $this->_validateValueRecursive($fieldName, array_merge($index, [$k]));
                }
                return null;
            }
            $isEmpty = empty($value['tmp_name']);
        } else {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->_validateValueRecursive($fieldName, array_merge($index, [$k]));
                }
                return null;
            }
            $isEmpty = is_array($value) ? empty($value) : (string)$value === '';
        }

        $errMesage =& $this->errorMsg[$fieldName];
        foreach ($index as $i) {
            if (!isset($errMesage[$i])) {
                $errMesage[$i] = null;
            }
            $errMesage =& $errMesage[$i];
        }

        // Check required value
        if (!empty($param['is_required']) && $isEmpty) {
            $errMesage = $this->_getErrorMesage($param['label'], $this->_getFormMeta('required_msg'));
            $this->isError = true;
        // Check value by specified rules
        } elseif (isset($param['validate_rules'])) {
            foreach ($param['validate_rules'] as $rules) {
                if (!$isEmpty || empty($rules['not_empty'])) {
                    $rule = $rules['rule_name'];
                    $validator = $this->_getValidator($rule);
                    if (!empty($validator)) {
                        if (!$validator->$rule($value, array_val($rules, 'rule_data'), $index)) {
                            $errMesage = isset($rules['error_msg']) ?
                                    $this->_getErrorMesage($param['label'], $rules['error_msg']) :
                                    '';
                            $this->isError = true;
                            break;
                        }
                    }
                }
            }
        }
        return $this;
    }

    protected function _onSubmitTransfer(mixed $allowTransfer, string $dbOper, bool $addQueryStr): static
    {
        $redirReq = $this->_getFormMeta('redirect_required');
        $redirUri = $this->_getFormMeta('redirect_uri');

        if (is_null($allowTransfer)) {
            $allowTransfer = is_null($redirReq) ?
                    strtoupper((string)$this->containerService('request')->get('REQUEST_METHOD', 'S')) === 'POST' :
                    !empty($redirReq);
        } elseif ($allowTransfer) {
            $allowTransfer = is_null($redirReq) || !empty($redirReq);
        }


        if ($allowTransfer) {
            if (empty($redirUri)) {
                $this->containerService('request')->remove('form_key_field', 'G');
                $tab = $this->block->getTab();
                $uri = $tab->getCurrentURI($this->isMultiLanguage(), true, $addQueryStr, true);
            } else {
                $uri = (string)$redirUri;
            }
            transfer_out($uri, null, $dbOper);
        }
        return $this;
    }

    protected function _getErrorMesage(string $label, string $msg, ?string $altMsg = null): mixed
    {
        if (empty($altMsg)) {
            $altMsg = $msg;
        }
        return $this->isMultiLanguage() ?
                msg($msg, $label) :
                msgAlt($msg, $label);
    }

    /**
     * @throws fatalException
     */
    protected function _getValidator(string $validatorName): mixed
    {
        if (!isset($this->validators[$validatorName])) {
            if (method_exists($this->block, $validatorName) && is_callable([$this->block, $validatorName])) {
                $this->validators[$validatorName] = $this->block;
            } else {
                if (!isset($this->validatorClasses[$validatorName])) {
                    throw new fatalException($this, 'Unknown Validator Name "' . $validatorName . '".');
                }
                $name = $this->validatorClasses[$validatorName];
                if ($name === 'string') {
                    $name = 'string_validator';
                }
                $engine = $this->_getEngine('validator\\' . $name);
                $this->validators[$validatorName] = empty($engine) ? null : $engine->setFacade($this);
            }
        }
        return $this->validators[$validatorName];
    }

    protected function _checkName(string $fieldName, bool $reportErr = true): bool
    {
        if (isset($this->fieldMeta[$fieldName])) {
            return true;
        }
        if ($reportErr) {
            throw new \OutOfBoundsException('Call incorrect field name "' . $fieldName . '" in the form "' . $this->block->getBlockName() . '"');
        }
        return false;
    }

    // ------------ Functions for main parts ------------ \\

    protected function _parseFormParts(mixed $parceEmpty): void
    {
        $parts = $this->_getFormMeta('form_parts', []);
        if (!empty($parts)) {
            try {
                $tab = $this->containerService('tab');
                /* @var $tab \fan\core\service\tab */
                foreach ($parts as $v) {
                    if ($tab->isSetBlock($v)) {
                        $subForm = $tab->getTabBlock($v)->getForm();
                        if ($subForm->parseForm($parceEmpty, true, false)) {
                            $this->partFieldValue[$v] = $subForm->getFieldValue();
                        } else {
                            $this->isError = true;
                        }
                    }
                }
            } catch (exception_error_form_part $e) {
                $this->isError = true;
                foreach ($e->getErrorMessages() as $k => $v) {
                    if (!empty($v)) {
                        if (empty($this->errorMsg[$k])) {
                            $this->errorMsg[$k] = $v;
                        } elseif (is_scalar($this->errorMsg[$k])) {
                            $this->errorMsg[$k] .= $v;
                        } else {
                            throw new fatalException($this, 'Can\'t set error message "' . $v . '".');
                        }
                    }
                }
            }
        }
    }

    protected function _getCombiParam(string $fieldName, array $index): array
    {
        $combiKey   = empty($index) ? null : $fieldName . '[' . implode('][', $index) . ']';
        $mainParam  = isset($this->fieldMeta[$fieldName]) ? adduceToArray($this->fieldMeta[$fieldName]) : [];
        $extraParam = isset($this->fieldMeta[$combiKey])  ? adduceToArray($this->fieldMeta[$combiKey])  : [];
        return array_merge_recursive_alt($mainParam, $extraParam);
    }

    protected function _transformFileData(array &$data, string $key, array $src): static
    {
        foreach ($src as $k => $v) {
            if (is_array($v)) {
                $data[$k] = [];
                $this->_transformFileData($data[$k], $key, $v);
            } else {
                $data[$k][$key] = $v;
            }
        }
        return $this;
    }

    // ======== The magic methods ======== \\

    /**
     * Handles dynamic property writes for this current component.
     *
     * @param mixed $value Value that should be applied or transformed.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setFieldValue($key, $value);
    }

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        return $this->getFieldValue($key);
    }

    /**
     * Checks whether a dynamic property is available.
     */
    public function __isset(string $key): bool
    {
        return isset($this->fieldValue[$key]);
    }
    // ======== Required Interface methods ======== \\

}
