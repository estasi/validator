# Changelog

## 1.2.0
### Added

- Property read _Estasi\Translator\Interfaces\Translator|null_ `Validator->translator`
- Property read _Ds\Map_ `Validator->errorMessagesAliases`
- Property read _string|null_ `Validator->errorValueAlias`
- Property read _int_ `Validator->errorMessageLength`
- Property read _bool_ `Validator->errorValueObscured`
- Const `Between::OPT_MIN`
- Const `Between::OPT_MAX`
- Property read _int_ `Between->min`
- Property read _bool_ `Between->minInclusive`
- Property read _int_ `Between->max`
- Property read _bool_ `Between->maxinclusive`
- Property read _string|null_ `Date->format`
- Property read _bool_ `Email->allowUnicode`
- Property read _int_ `GreaterThan->min`
- Property read _bool_ `GreaterThan->inclusive`
- Property read _mixed_ `Identical->token`
- Property read _bool_ `Identical->strict`
- Property read _int_ `LessThan->max`
- Property read _bool_ `LessThan->inclusive`
- Property read _array_ `Regex->pattern` _`['pcre' => 'string', 'html' => 'string']`_
- Property read _int_ `Regex->offset`
- Property read _float_ `Step->step`
- Property read _float_ `Step->startingPoint`
- Const `StringLength::OPT_ENCODING`
- Property read _int_ `StringLength->min`
- Property read _int_ `StringLength->max`
- Property read _string_ `StringLength->encoding`
- Now when initializing the `Estasi\Validator\StringLength` class if min is greater than max an exception is thrown `\RuntimeException`

### Changed

- Method `PluginManager::getValidator` moved to trait `Traits\PluginManager`

### Fixed

- Error during class initialization `GreaterThan`
- Error during class initialization `LessThan`

## 1.1.0

### Added

- Method `Chain::getValidators`
- Method `Each::getValidator`