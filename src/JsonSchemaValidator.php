<?php

    namespace magp\yii2jsv;

    use Swaggest\JsonSchema\InvalidValue;
    use Yii;
    use yii\helpers\Json;
    use yii\validators\Validator;
    use Swaggest\JsonSchema\Schema;
    use yii\base\InvalidConfigException;

    /**
     * JsonSchemaValidator validates a value against a JSON Schema file.
     *
     * The URI of the schema file must be defined via the [[schema]] property.
     *
     * @author Manuel Gonzalez <manuel_alejandrogp@web.com>
     */
    class JsonSchemaValidator extends Validator
    {
        /**
         * @var string The URI of the JSON schema file.
         */
        public $schema;

        /**
         * @var string User-defined error message used when the schema is missing.
         */
        public $schemaEmpty;
        /**
         * @var string User-defined error message used when the schema isn't a string.
         */
        public $schemaNotString;
        /**
         * @var string User-defined error message used when the value is not a string.
         */
        public $notString;
        /**
         * @var string User-defined error message used when the value is not a valid JSON string.
         */
        public $notJsonString;
        /**
         * @var string User-defined error message used when the value is not an array
         */
        public $notArray;

        /**
         * @throws \yii\base\InvalidConfigException
         */
        public function init()
        {
            parent::init();

            if ($this->schemaEmpty === null) {
                $this->schemaEmpty = 'The "schema" property must be set.';
            }
            if ($this->schemaNotString === null) {
                $this->schemaNotString = 'The "schema" property must be a a string.';
            }
            if ($this->message === null) {
                $this->message = Yii::t('app', '{property}: {message}.');
            }
            if ($this->notString === null) {
                $this->notString = Yii::t('app', 'The value must be a string.');
            }
            if ($this->notJsonString === null) {
                $this->notJsonString = Yii::t('app', 'The value must be a valid JSON string.');
            }

            //Check if its empty
            if (empty($this->schema)) {
                throw new InvalidConfigException($this->schemaEmpty);
            }

            //Check if not string
            if (!is_string($this->schema)) {
                throw new InvalidConfigException($this->schemaNotString);
            }
        }

        /**
         * @param \yii\base\Model $model
         * @param string          $attribute
         *
         * @return void|null
         * @throws \yii\base\NotSupportedException
         */
        public function validateAttribute($model, $attribute)
        {
            // Check if not string
            if (!is_string($model->$attribute)) {
                $this->addError($model, $attribute, $this->notString);
                return null;
            }

            // Validate de Json
            $validate = $this->validateValue($model->$attribute);

            if($validate !== null){
                if($validate->subErrors !== null){
                    foreach($validate->subErrors as $exceptions){
                        $this->addError($model, $attribute, $exceptions->error.'. Check in path: '.$validate->dataPointer);
                    }
                } else {
                    $this->addError($model, $attribute, 'Error!... '.$validate->error.'. Check in path: '.$validate->dataPointer);
                }
                return null;
            }/**
         * @param mixed $value
         *
         * @return array|string|null
         * @throws \Swaggest\JsonSchema\Exception
         * @throws \Swaggest\JsonSchema\InvalidValue
         */
        }


        protected function validateValue($value)
        {
            $schema = Schema::import($this->schema);
            try {
                $schema->in(json_decode($value));
            }
            catch(InvalidValue $e) {
                $error = $e->inspect();
                return $error;
            }
            return null;
        }
    }
