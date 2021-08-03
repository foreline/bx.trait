<?php
    /**
     * Трейт для работы с информационными блоками Битрикса
     * 
     * @package bitrix
     * @subpackage iblock
     * @subpackage bitrix
     * 
     * @author dima@foreline.ru
     */
    
    trait bx {
        
        /** @var string $errorMessage Сообщение об ошибке */
        public string $errorMessage = '';
        
        /** @var int $iblockID ID инфоблока */
        public int $iblockID = 0;

        /** @var string Символьный код инфоблока */
        public string $iblockCode = '';
        
        /** @var string $iblockName Название инфоблока */
        public string $iblockName = '';

        /** @var string $iblockType Тип инфоблока */
        public string $iblockType = '';

        /** @var string $iblockDescription Описание инфоблока */
        public string $iblockDescription = '';
        
        /** @var int $iblockSort Сортировка инфоблока */
        public int $iblockSort = 0;
        
        /** @var string $listPageUrl URL страницы информационного блока */
        public string $listPageUrl = '';
        
        /** @var string $sectionPageUrl URL страницы раздела */
        public string $sectionPageUrl = '';
        
        /** @var string $elementName Название элемента инфоблока из настроек инфоблока. Заполняется методом getIBlockInfo() */
        public string $elementName = '';
        
        /** @var string $elementsName Название элементов инфоблока из настроек инфоблока. Заполняется методом getIBlockInfo() */
        public string $elementsName = '';
        
        /** @var string $sectionName Название раздела инфоблока из настроек инфоблока. Заполняется методом getIBlockInfo() */
        public string $sectionName = '';
        
        /** @var string $sectionsName Название разделов инфоблока из настроек инфоблока. Заполняется методом getIBlockInfo() */
        public string $sectionsName = '';
        
        /** @var string $elementAdd Подписи и заголовки объектов: Добавить элемент */
        public string $elementAdd = '';
        
        /** @var string $elementEdit Подписи и заголовки объектов: Изменить элемент */
        public string $elementEdit = '';
        
        /** @var string $elementDelete Подписи и заголовки объектов: Удалить элемент */
        public string $elementDelete = '';

        /**
         * Конструктор
         * @return void
         * @throws \Bitrix\Main\LoaderException
         */
        public function __construct()
        {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
            $this->getIBlockInfo();
        }

        /**
         * Возвращает массив, описывающий элемент по его ID
         *
         * @param int $elementID ID элемента инфоблока
         * @param array $arFilter [optional] Дополнительные ключи для фильтра. По умолчанию выборка производится по-указанному ID элемента и текущему инфоблоку.
         * @return array|bool $arElement Массив, описывающий элемент инфоблока
         */
        public function getByID(int $elementID, array $arFilter = [])
        {
            if ( 0 >= $elementID ) {
                $this->errorMessage = 'Не задан ID элемента ' . $this->elementName;
                return false;
            }

            $arDefaultFilter = [
                'IBLOCK_ID'     => $this->iblockID,
                'ID'            => $elementID,
            ];

            $arFilter = array_merge($arDefaultFilter, $arFilter);
            
            $res = CIBlockElement::GetList(
                [],
                $arFilter
            );
            
            if ( !$ar_res = $res->GetNextElement() ) {
                $this->errorMessage = 'Элемент ' . $this->elementName . ' не найден';
                return false;
            }

            $arItem = $ar_res->GetFields();
            $arItem['PROPERTIES'] = $ar_res->GetProperties();
            
            return $arItem;
        }
        
        /**
         * Возвращает сокращенный массив, описывающий элемент по его ID.
         * Свойства представлены как ['КОД_СВОЙСТВА' => 'ЗНАЧЕНИЕ_СВОЙСТВА'] 
         * 
         * @param int $elementID ID элемента инфоблока
         * @return array|bool $arElement Массив, описывающий элемент инфоблока
         */
        public function _getByID(int $elementID)
        {
            if ( 0 >= $elementID ) {
                $this->errorMessage = 'Не задан ID элемента ' . $this->elementName;
                return false;
            }
            
            $res = CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_ID'     => $this->iblockID,
                    'ID'            => $elementID,
                ]
            );
            
            if ( !$ar_res = $res->GetNextElement() ) {
                $this->errorMessage = 'Элемент ' . $this->elementName . ' не найден';
                return false;
            }
            
            $arItem = $ar_res->GetFields();
            $arItem['PROPERTIES'] = $ar_res->GetProperties();
            
            $arElement = [];
            
            $arElement['ID'] = $arItem['ID'];
            $arElement['NAME'] = $arItem['NAME'];
            $arElement['CODE'] = $arItem['CODE'];
            $arElement['ACTIVE'] = $arItem['ACTIVE'];
            $arElement['CREATED_BY'] = $arItem['CREATED_BY'];
            $arElement['DATE_CREATE'] = $arItem['DATE_CREATE'];
            $arElement['MODIFIED_BY'] = $arItem['MODIFIED_BY'];
            $arElement['TIMESTAMP_X'] = $arItem['TIMESTAMP_X'];
            
            foreach ( $arItem['PROPERTIES'] as $arProperty ) {
                $arElement[$arProperty['CODE']] = $arProperty['VALUE'];
            }
            
            return $arElement;
        }

        /**
         * Возвращает массив, описывающий элемент по его ID без свойств.
         * 
         * @param int $elementID ID элемента инфоблока
         * @return array|bool $arElement Массив, описывающий элемент инфоблока
         */
        public function __getByID(int $elementID)
        {
            if ( 0 >= $elementID ) {
                $this->errorMessage = 'Не задан ID элемента ' . $this->elementName;
                return false;
            }
            
            $res = CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_ID'     => $this->iblockID,
                    'ID'            => $elementID,
                ]
            );
            
            if ( !$ar_res = $res->GetNextElement() ) {
                $this->errorMessage = 'Элемент ' . $this->elementName . ' не найден';
                return false;
            }

            return $ar_res->GetFields();
        }
        
        /**
         * Возвращает массив, описывающий элемент по символьному коду элемента
         * 
         * @param string $elementCode Символьный код элемента инфоблока
         * @return array|bool $arElement Массив, описывающий элемент инфоблока
         */
        
        public function getByCode(string $elementCode)
        {
            if ( empty($elementCode) ) {
                $this->errorMessage = 'Не задан символьный код элемента '  . $this->elementName;
                return false;
            }
            
            $res = CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_ID' => $this->iblockID,
                    'CODE'      => $elementCode,
                ]
            );
            
            if ( !$ar_res = $res->GetNextElement() ) {
                $this->errorMessage = 'Элемент ' . $this->elementName . ' не найден';
                return false;
            }
            
            $arItem = $ar_res->GetFields();
            $arItem['PROPERTIES'] = $ar_res->GetProperties();
            
            return $arItem;
        }

        /**
         * Заполняет свойства класса, такие как iblockID, iblockName, elementName и т.п.
         * Вызывается в конструкторе класса
         * @return bool $result
         * @throws \Bitrix\Main\LoaderException
         */
        public function getIBlockInfo(): bool
        {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
            if ( ! \Bitrix\Main\Loader::includeModule('iblock') ) {
                $this->errorMessage = 'Модуль "Инфоблоки" не установлен';
                return false;
            }
            
            $res = CIBlock::GetList(
                [],
                [
                    'CODE'  => $this->iblockCode,
                    'CHECK_PERMISSIONS' => 'N',
                ]
            );
            
            if ( !$arIBlock = $res->GetNext() ) {
                $this->errorMessage = 'Инфоблок не найден';
                return false;
            }
            
            $this->iblockID = $arIBlock['ID'];
            $this->iblockType = $arIBlock['IBLOCK_TYPE_ID'];
            $this->iblockName = $arIBlock['NAME'];
            $this->iblockDescription = $arIBlock['DESCRIPTION'];
            
            $this->iblockSort = $arIBlock['SORT'];
            
            $listPageUrl = str_replace('#SITE_DIR#', SITE_DIR, $arIBlock['LIST_PAGE_URL']);
            $listPageUrl = str_replace('#IBLOCK_CODE#', $this->iblockCode, $listPageUrl);
            $listPageUrl = str_replace('#IBLOCK_ID#', $this->iblockID, $listPageUrl);
            $listPageUrl = str_replace('#IBLOCK_TYPE_ID#', $this->iblockType, $listPageUrl);
            $this->listPageUrl = str_replace('//', '/', $listPageUrl);
            
            $sectionPageUrl = str_replace('#SITE_DIR#', SITE_DIR, $arIBlock['SECTION_PAGE_URL']);
            $sectionPageUrl = str_replace('#IBLOCK_CODE#', $this->iblockCode, $sectionPageUrl);
            $sectionPageUrl = str_replace('#IBLOCK_ID#', $this->iblockID, $sectionPageUrl);
            $sectionPageUrl = str_replace('#IBLOCK_TYPE_ID#', $this->iblockType, $sectionPageUrl);
            $this->sectionPageUrl = str_replace('//', '/', $sectionPageUrl);
            
            /*
             * 
             */
            
            $arIBlockMessages = CIBlock::GetMessages($this->iblockID);
            
            $this->elementName = $arIBlockMessages['ELEMENT_NAME'];
            $this->elementsName = $arIBlockMessages['ELEMENTS_NAME'];
            $this->elementAdd = $arIBlockMessages['ELEMENT_ADD'];
            $this->elementEdit = $arIBlockMessages['ELEMENT_EDIT'];
            $this->elementDelete = $arIBlockMessages['ELEMENT_DELETE'];
            
            $this->sectionName = $arIBlockMessages['SECTION_NAME'];
            $this->sectionsName = $arIBlockMessages['SECTIONS_NAME'];

            return true;
        }
        
        /**
         * Возвращает массив элементов инфоблока
         * 
         * @param array [optional] $arSort
         * @param array [optional] $arFilter Фильтр. По умолчанию фильтрация идет по ID инфоблока текущего класса
         * 
         * @return array $arIBlockElements
         */
        public function getList(array $arSort = [], array $arFilter = []): array
        {
           
           if ( !isset($arFilter['IBLOCK_ID']) ) {
               $arFilter['IBLOCK_ID'] = $this->iblockID;
           }
           
           $res = CIBlockElement::GetList(
               $arSort,
               $arFilter
           );
           
           $arItems = [];
           
           while ( $ar_res = $res->GetNextElement() ) {
               $arItem = $ar_res->GetFields();
               $arItem['PROPERTIES'] = $ar_res->GetProperties();
               
               $arItems[] = $arItem;
           }
           
           return $arItems;
        }
        
        /**
         * Возвращает объект CDataBase для самостоятельной работы с объектом
         * 
         * @param array [optional] $arSort
         * @param array [optional] $arFilter Фильтр. По умолчанию фильтрация идет по ID инфоблока текущего класса
         * 
         * @return object $CDataBase
         */
        public function _getList(array $arSort = [], array $arFilter = []): object
        {
           
           $arFilter['IBLOCK_ID'] = (int) ( $arFilter['IBLOCK_ID'] ?? $this->iblockID );
           
           return CIBlockElement::GetList(
               $arSort,
               $arFilter
           );
        }

        /**
         * Синоним метода _add
         * @param array $arFields
         * @return int @itemID ID добавленного элемента. Вернет 0, если произошла ошибка.
         */
        public function add(array $arFields): int
        {
            return $this->_add($arFields);
        }
        
        /**
         * Добавление элемента инфоблока и его свойств.
         * Выполняется проверка на заполнение обязательных полей только если они заданы.
         * Вызывает методы beforeAdd($arFields) и afterAdd($arFields).
         * 
         * @param array $arFields Массив со значениями полей элемента и его свойствами.
         * $arFields = [
         *  'NAME'      => 'Название',
         *  'ACTIVE'    => 'Y',
         *  'SORT'      => 500,
         *  'PROPERTY_CODE' => 'PROPERTY_VALUE',
         * ]
         * @return int $itemID ID добавленного элемента. Вернет 0, если произошла ошибка.
         */
        public function _add(array $arFields = []): int
        {
            global $USER;
            $iblockElement = new CIBlockElement;
            
            $arIBlockFields = [
                'MODIFIED_BY'       => $USER->GetID(),
                'IBLOCK_ID'         => $this->iblockID,
                'ACTIVE'            => 'Y',
            ];
            
            // Название
            if ( !empty($arFields['NAME']) ) {
                $arIBlockFields['NAME'] = $arFields['NAME'];
            } else {
                $arIBlockFields['NAME'] = $this->elementName;
                $arFields['NAME'] = $this->elementName;
            }
            
            // Символьный код
            if ( isset($arFields['CODE']) ) {
                $arIBlockFields['CODE'] = $arFields['CODE'];
            }
            
            // Привязка к разделу
            $arIBlockFields['IBLOCK_SECTION_ID'] = (0 < intval($arFields['IBLOCK_SECTION_ID']) ? intval($arFields['IBLOCK_SECTION_ID']) : false);
            
            // Активность
            $arIBlockFields['ACTIVE'] = (!empty($arFields['ACTIVE']) && 'N' == $arFields['ACTIVE'] ? 'N' : 'Y');
            
            // Сортировка
            if ( !empty($arFields['SORT']) && 0 <= intval($arFields['SORT']) ) {
                $arIBlockFields['SORT'] = intval($arFields['SORT']);
            }
            
            // Текст анонса
            if ( !empty($arFields['PREVIEW_TEXT']) ) {
                $arIBlockFields['PREVIEW_TEXT'] = $arFields['PREVIEW_TEXT'];
            }
            
            // Детальный текст
            if ( !empty($arFields['DETAIL_TEXT']) ) {
                $arIBlockFields['DETAIL_TEXT'] = $arFields['DETAIL_TEXT'];
            }
            
            $this->beforeAdd($arIBlockFields);
            
            global $DB;
            $DB->StartTransaction();

            /*
             * Обработка свойств
             */

            $arProperties = [];

            $arPossibleProperties = $this->getIBlockProperties();

            $arPossiblePropertiesIDs = [];
            foreach ( $arPossibleProperties as $arPossibleProperty ) {
                $arPossiblePropertiesIDs[] = $arPossibleProperty['ID'];
            }

            foreach ( $arFields as $key => $value ) {
                if ( !key_exists($key, $arPossibleProperties) && !in_array($key, $arPossiblePropertiesIDs) ) {
                    continue;
                }

                if ( 'Y' == $arPossibleProperties[$key]['IS_REQUIRED'] & empty($value) ) {
                    $this->errorMessage = 'Поле ' . $arPossibleProperties[$key]['NAME'] . ' обязательно';
                    return 0;
                }

                $arProperties[$key] = $value;
            }

            $arIBlockFields['PROPERTY_VALUES'] = $arProperties;

            if ( !$itemID = $iblockElement->Add($arIBlockFields) ) {
                $this->errorMessage = 'Ошибка при добавлении элемента (' . $this->elementName . '): ' . $iblockElement->LAST_ERROR;
                $DB->RollBack();
                return 0;
            }

            $DB->Commit();
            
            $this->afterAdd($arIBlockFields);
            
            return $itemID;
        }
        
        /**
         * Функция для обработки полей перед добавлением
         * @param array $arFields Поля элемента
         * @return void
         */
        public function beforeAdd(array &$arFields)
        {
            // Какие-либо действия с полями перед добавлением
        }
        
        /**
         * Метод, вызываемый после добавления элемента
         * @param array $arFields Поля элемента
         * @return void
         */
        public function afterAdd(array &$arFields)
        {
            // Действия после добавления элемента
        }

        /**
         * Синоним метода _update
         * @param int $itemID
         * @param array $arFields
         * @return bool
         */
        public function update(int $itemID, array $arFields = []): bool
        {
            return $this->_update($itemID, $arFields);
        }
        
        /**
         * Обновление элемента инфоблока и его свойств.
         * 
         * @param int $itemID ID элемента инфоблока
         * @param array $arFields Массив с полями элемента, а также его свойствами. Свойства можно задавать через КОД свойства (без префикса PROPERTY_), а также через его ID
         * @return bool $result
         */
        public function _update(int $itemID, array $arFields = []): bool
        {
            if ( 0 >= $itemID ) {
                $this->errorMessage = 'Не задан ID элемента (' . $this->elementName . ')';
                return false;
            }
            
            $this->beforeUpdate($arFields);
            
            $iblockElement = new CIBlockElement;
            
            /*
             * Обновление полей элемента
             */
            
            $updateElement = false;
            
            $arPossibleFields = [
                'NAME',
                'CODE',
                'SORT',
                'ACTIVE',
                'IBLOCK_SECTION_ID',
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
            ];
            
            foreach ( $arFields as $key => $value ) {
                if ( in_array($key, $arPossibleFields) ) {
                    $updateElement = true;
                    break;
                }
            }
            
            if ( true === $updateElement ) {
                if ( !$iblockElement->Update($itemID, $arFields) ) {
                    $this->errorMessage = 'Ошибка при обновлении элемента (' . $this->elementName . '): ' . $iblockElement->LAST_ERROR;
                    return false;
                }
            }
            
            /*
             * Обновление свойств элемента
             */
            $arProperties = [];
            
            $arPossibleProperties = $this->getIBlockProperties();
            
            $arPossiblePropertiesIDs = [];
            foreach ( $arPossibleProperties as $arPossibleProperty ) {
                $arPossiblePropertiesIDs[] = $arPossibleProperty['ID'];
            }
            
            foreach ( $arFields as $key => $value ) {
                if ( !key_exists($key, $arPossibleProperties) && !in_array($key, $arPossiblePropertiesIDs) ) {
                    continue;
                }
                    
                if ( 'Y' == $arPossibleProperties[$key]['IS_REQUIRED'] & empty($value) ) {
                    $this->errorMessage = 'Поле ' . $arPossibleProperties[$key]['NAME'] . ' обязательно';
                    return false;
                }
                
                $arProperties[$key] = $value;
            }
            
            foreach ( $arProperties as $propertyCode => $propertyValue ) {
                $iblockElement->SetPropertyValues($itemID, $this->iblockID, $propertyValue, $propertyCode);
            }
            
            $this->afterUpdate($arFields);
            
            return true;
        }
        
        /**
         * Метод, вызываемый перед обновлением элемента
         * @param array $arFields Ссылка на массив с полями элемента
         * @return void
         */
        public function beforeUpdate(array &$arFields)
        {
            
        }
        
        /**
         * Метод, вызываемый после обновления элемента
         * @param array $arFields Ссылка на массив с полями элемента
         * @return void
         */
        public function afterUpdate(array &$arFields)
        {
            
        }

        /**
         * Активирует элемент инфоблока
         * 
         * @param int $itemID ID элемента
         * @return bool $result
         */
        public function activate(int $itemID): bool
        {
            return $this->_update($itemID, ['ACTIVE' => 'Y']);
        }
        
        /**
         * Деактивирует элемент инфоблока
         * @param int $itemID ID элемента
         * @return bool $result
         */
        public function deactivate(int $itemID): bool
        {
            return $this->_update($itemID, ['ACTIVE' => 'N']);
        }
        
        /**
         * Возвращает массив свойств текущего инфоблока (без их значений). Ключи массива это код свойства.
         * @return array $arProperties
         */
        public function getIBlockProperties(): array
        {
            $iblock = new CIBlock;
            
            $res = $iblock->GetProperties(
                $this->iblockID,
                $arOrder = [],
                $arFilter = [
                    'CHECK_PERMISSIONS' => 'N',
                ]
            );
            
            $arProperties = [];
            
            while ( $arProperty = $res->Fetch() ) {
                $arProperties[$arProperty['CODE']] = $arProperty;
            }
            
            return $arProperties;
        }
        
        /**
         * Возвращает список значений свойства типа "список". Является оберткой для метода CIBlockPropertyEnum::GetList
         * 
         * @param string|int $propertyCode Символьный код свойства, либо его ID. Рекомендуется использовать ID свойство, так как символьный код не уникален
         * @return array|bool $arPropertyList
         */
        public function getPropertyEnumList($propertyCode = '')
        {
            $varType = gettype($propertyCode);
            
            $arFilter = [
                'IBLOCK_ID' => $this->iblockID,
            ];
            
            if ( 'integer' == $varType || intval($propertyCode) == $propertyCode ) {
                $arFilter['PROPERTY_ID'] = $propertyCode;
            } else if ( 'string' == $varType ) {
                $arFilter['CODE'] = $propertyCode;
            } else {
                $this->errorMessage = 'Неверный тип параметра';
                return false;
            }
            
            $res = CIBlockPropertyEnum::GetList(
                [
                    'DEF' => 'DESC',
                    'SORT' => 'ASC',
                ],
                $arFilter
            );
            
            $arPropertyList = [];
            
            while ( $arFields = $res->GetNext() ) {
                $arPropertyList[] = $arFields;
            }
            
            return $arPropertyList;
        }
        
        /**
         * Проверка прав на доступ (чтение) элемента инфоблока. Синоним метода userCanRead
         * 
         * @param int $elementID ID элемента инфоблока
         * @param int $userID ID пользователя
         * @return bool $userHasAccess
         */
        public function userHasAccess(int $elementID, int $userID): bool
        {
            return $this->userCanRead($elementID, $userID);
        }
        
        /**
         * @TODO
         * Проверка прав на доступ (чтение) элемента инфоблока. Синоним метода userCanAccess
         * 
         * @param int $elementID ID элемента инфоблока
         * @param int $userID ID пользователя
         * @return bool $userCanRead
         */
        public function userCanRead(int $elementID, int $userID = USER_ID): bool
        {
            /** @TODO Реализовать метод */
            return false;
        }
        
        /**
         * Проверяет есть ли у пользователя права на редактирование (изменение) (в том числе полный доступ) элемента инфоблока.
         * Проверка осуществляется при расширенных правах доступа на инфоблок.
         * Учитываются права заданные на конкретного пользователя, так и на группы, в которых состоит пользователь.
         * 
         * @param int $elementID ID элемента инфоблока
         * @param int [optional] $userID ID пользователя, если не задан, то проверка осуществляется для текущего пользователя
         * 
         * @return bool $userCanEdit
         */
        public function userCanEdit(int $elementID, int $userID = USER_ID): bool
        {
            if ( 0 >= $elementID ) {
                return false;
            }
            
            global $USER;

            /** @FIXME CUser::GetUserGroupArray работает только с текущим пользователем */
            $arGroups = $USER->GetUserGroupArray();
            
            $groupCode = '(' . '"U' . USER_ID . '", "G' . implode('", "G', $arGroups) . '"' . ')'; 
            
            global $DB;
            
            $query = '
            SELECT *
            FROM `b_iblock_element_right` as IER
                INNER JOIN `b_iblock_right` as IR ON IR.`ID` = IER.`RIGHT_ID`
                INNER JOIN `b_task` as T on T.`ID` = IR.`TASK_ID`
            WHERE
                IR.`ENTITY_TYPE` IN (\'element\', \'iblock\')
                AND IER.`IBLOCK_ID` = ' . $this->iblockID . '
                AND IER.`ELEMENT_ID` = ' . $elementID . '
                AND IR.`GROUP_CODE` IN ' . $groupCode . '
                AND T.`NAME` IN (\'iblock_full_edit\', \'iblock_edit\', \'iblock_full\')
            ';
            
            $res = $DB->Query($query, true);
            
            if ($res->Fetch()) {
                return true;
            }
            
            return false;
        }

        /**
         * Возвращает подразделы заданного раздела инфоблока
         *
         * @param int $sectionID ID родительского раздела, подразделы которого необходимо выбрать
         * @param array $arOrder
         * @param array $arFilter
         * @return array|bool $arSections
         */
        public function getSections(int $sectionID, array $arOrder = [], array $arFilter = [])
        {
            if ( 0 > $sectionID ) {
                $this->errorMessage = 'Не задан ID раздела';
                return false;
            }
            
            if ( !$res = $this->_getSections($sectionID, $arOrder, $arFilter) ) {
                return false;
            }
            
            $arSections = [];
            
            while ( $arSection = $res->GetNext() ) {
                $arSections[] = $arSection;
            }
            
            return $arSections;
        }

        /**
         * Возвращает подразделы заданного раздела инфоблока
         *
         * @param int $sectionID ID родительского раздела, подразделы которого необходимо выбрать
         * @param array $arOrder
         * @param array $arFilter
         * @return object|bool $arSections
         */
        public function _getSections(int $sectionID, array $arOrder = [], array $arFilter = [])
        {
            if ( 0 > $sectionID ) {
                $this->errorMessage = 'Не задан ID раздела';
                return false;
            }

            $arFilter['IBLOCK_ID'] = $this->iblockID;
            $arFilter['SECTION_ID'] = $sectionID;

            return CIBlockSection::GetList(
                $arOrder,
                $arFilter,
            );
        }
        
        /**
         * Возвращает раздел по его ID
         * @param int $sectionID ID раздела
         * @param array $arFilter Фильтр
         * @return bool|array $arSection
         */
        public function getSectionByID(int $sectionID, array $arFilter = [])
        {
            if ( 0 >= $sectionID ) {
                $this->errorMessage = 'Не указан ID раздела';
                return false;
            }
            
            $arFilter['IBLOCK_ID'] = $this->iblockID;
            $arFilter['ID']        = $sectionID;
            
            $res = CIBlockSection::GetList(
                [],
                $arFilter,
            );
            
            if ( !$arSection = $res->GetNext() ) {
                $this->errorMessage = 'Раздел ' . $sectionID . ' не найден';
                return false;
            }
            
            return $arSection;
        }
        
        /**
         * Возвращает свойство по его ID
         * @param int $propertyID ID свойства
         * @return bool|array $arProperty
         */
        public function getPropertyByID(int $propertyID)
        {
            if ( 0 >= $propertyID ) {
                $this->errorMessage = 'Не указан ID свойства';
                return false;
            }

            return CIBlockProperty::GetByID($propertyID)->Fetch();
        }
        
        /**
         * Возвращает массив для построения цепочки навигации до заданного раздела
         * 
         * @param int $sectionID ID раздела инфоблока
         * @return array $arNavChain
         */
        public function getNavChain(int $sectionID): array
        {
            $navRes = CIBlockSection::GetNavChain($this->iblockID, $sectionID);
            
            $arNavChain = [];
            
            while ( $arNav = $navRes->GetNext() ) {
                $arNavChain[] = $arNav;
            }
            
            return $arNavChain;
        }

        /**
         * Синоним метода getPropertyValue()
         * @param int $itemID
         * @param string $propertyCode
         * @return false|mixed
         */
        public function getPropertyVal(int $itemID, string $propertyCode)
        {
            return $this->getPropertyValue($itemID, $propertyCode);
        }

        /**
         * Возвращает значение свойства для указанного элемента инфоблока.
         * @param int $itemID ID элемента инфоблока
         * @param string $propertyCode Символьный код свойства
         * @return mixed $propertyValue
         */
        public function getPropertyValue(int $itemID, string $propertyCode)
        {
            $res = CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_ID' => $this->iblockID,
                    'ID'        => $itemID,
                ],
                false,
                false,
                [
                    'ID',
                    'IBLOCK_ID',
                    'PROPERTY_' . $propertyCode
                ]
            );
            
            if ( !$arElement = $res->Fetch() ) {
                return false;
            }

            return $arElement['PROPERTY_' . $propertyCode . '_VALUE'];
        }

        /**
         * Возвращает массив, описывающий свойство типа "список" по заданному коду свойства и заданному коду XML_ID
         * @param string $propertyCode Символьный код свойства
         * @param string $xmlID XML_ID код значения свойства
         * @return array|bool $arPropertyEnum ID значения свойства
         */
        function getPropertyEnum(string $propertyCode, string $xmlID)
        {
            if ( empty($propertyCode) ) {
                $this->errorMessage = 'Не задан код свойства';
                return false;
            }
            if ( empty($xmlID) ) {
                $this->errorMessage = 'Не задан XML_ID значения свойства';
                return false;
            }

            $res = CIBlockPropertyEnum::GetList(
                [],
                [
                    'XML_ID'    => $xmlID,
                    'CODE'      => $propertyCode,
                    'IBLOCK_ID' => $this->iblockID,
                ]
            );

            if ( !$arPropertyEnum = $res->Fetch() ) {
                return false;
            }

            return $arPropertyEnum;
        }

        /**
         * Возвращает ID значения свойства типа "список" по заданному коду свойства и заданному коду XML_ID значения свойства
         * @param string $propertyCode Символьный код свойства
         * @param string $xmlID XML_ID код значения свойства
         * @return int|bool $propertyID ID значения свойства
         */
        function getPropertyEnumID(string $propertyCode, string $xmlID)
        {
            if ( !$arProperty = $this->getPropertyEnum($propertyCode, $xmlID) ) {
                return false;
            }

            return (int) $arProperty['ID'];
        }

        /**
         * Возвращает текстовое значение свойства типа "список" по заданному коду свойства и заданному коду XML_ID
         * @param string $propertyCode Символьный код свойства
         * @param string $xmlID XML_ID код значения свойства
         * @return string|bool $propertyValue текстовое значение свойства
         */
        function getPropertyEnumValue(string $propertyCode, string $xmlID)
        {
            if ( !$arProperty = $this->getPropertyEnum($propertyCode, $xmlID) ) {
                return false;
            }

            return $arProperty['VALUE'];
        }
    }
    