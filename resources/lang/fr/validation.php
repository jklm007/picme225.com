<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':Attribut doit être accepté.',
    'active_url'           => ':Atrtibut est un URL invalide.',
    'after'                => ':Attribut doit être une date après :date.',
    'after_or_equal'       => ':Attribut doit être une date après ou égale à :date.',
    'alpha'                => ':Attribut ne peut contenir que des lettres.',
    'alpha_dash'           => ':Attribut ne peut contenir que des lettres, chiffred, et des tirets.',
    'alpha_num'            => ':Attribut ne peut contenir que des lettres et des chiffres.',
    'array'                => ':Attribut doit être un tableau.',
    'before'               => ':Attribut doit être une date avant :date.',
    'before_or_equal'      => ':Attribut doit être une date antérieure ou égale à la date.',
    'between'              => [
        'numeric' => ':Attribut doit être compris entre :min et :max.',
        'file'    => ':Attribut doit être compris entre :min et :max kilobytes.',
        'string'  => ':Attribut doit être compris entre des caractères :min et :max.',
        'array'   => ':Attribut doit être compris entre :min and :max items.',
    ],
    'boolean'              => ':Attribut doit être vrai ou faux',
    'confirmed'            => ':Attribut confirmation ne correspond pas.',
    'date'                 => ':Attribut une date invalide.',
    'date_format'          => ':Attribut ne correspond pas au format :format.',
    'different'            => ':Attribut et les :other doivent être différents.',
    'digits'               => ':Attribut doit être un :digits chiffres.',
    'digits_between'       => ':Attribut doit être compris entre les chiffres :min et :max chiffres.',
    'dimensions'           => ':Attribut a des dimensions images incorrectes.',
    'distinct'             => 'Le champ :attribut a une valeur en double .',
    'email'                => ':Attribut doit être une adresse email valide.',
    'exists'               => ':Attribut sélectionné est invalide.',
    'file'                 => ':Attribut doit être un fichier.',
    'filled'               => 'Le champ :attribut est obligatoire.',
    'image'                => ':Attribut doit être une image.',
    'in'                   => ':Attribute sélectionné est invalide.',
    'in_array'             => 'Le champ :attribut ne sort pas dans les :other.',
    'integer'              => ':Attribut doit être un nombre entier .',
    'ip'                   => ':Attribut doit être une adresse IP valide.',
    'json'                 => ':Attribut doit être une chaîne JSON valide.',
    'max'                  => [
        'numeric' => ':Attribut ne peut pas être supérieur à :max.',
        'file'    => ':Attribut ne doit pas être supérieur à :max kilobytes.',
        'string'  => ':Attribut ne peut pas être supérieur á :max caractères.',
        'array'   => ':Attribut ne doit pas contenir plus :max éléments.',
    ],
    'mimes'                => ':Attribut doit être un fichier de type: :values.',
    'mimetypes'            => ':Attribut doit être un fichier de type: :values.',
    'min'                  => [
        'numeric' => ':Attribut doit être au moins :min.',
        'file'    => ':Attribut doit être au moins :min kilobytes.',
        'string'  => ':Attribut doit avoir au moins :min caractères.',
        'array'   => ':Attribut doit avoir au moins :min éléments.',
    ],
    'not_in'               => ':Attribut sélectionné est invalid.',
    'numeric'              => ':Attribut doit être un nombre.',
    'present'              => 'Le champ :attribut doit être présent.',
    'regex'                => 'Le format :attribut est invalide.',
    'required'             => 'Le champ :attribut est obligatoire.',
    'required_if'          => 'Le champ :attribut est requis lorsque un autre est la valeur.',
    'required_unless'      => 'Le champ :attribut est requis sauf si un autre est en valeurs.',
    'required_with'        => 'Le champ :attribut est requis lorsque des valeurs sont présentes.',
    'required_with_all'    => 'Le champ :attribut est requis lorsque des valeurs sont présentes.',
    'required_without'     => 'Le champ :attribut est obligatoire lorsque les valeurs ne sont pas présentes.',
    'required_without_all' => 'Le champ :attribut est requis lorsque aucune des valeurs est présentes.',
    'same'                 => ':Attribut et :autre doivent correspondre.',
    'size'                 => [
        'numeric' => ':Attribut doit être de taille.',
        'file'    => ':Attribut doit être de taille kilo octets.',
        'string'  => ':Attribut doit être des caractères de taille.',
        'array'   => ':Attribut doit contenir des éléments de taille.',
    ],
    'string'               => ':Attribut doit être une chaîne.',
    'timezone'             => ':Attribut doit être une zone valide.',
    'unique'               => ':Attribut a déja été pris.',
    'uploaded'             => ':Attribut a pas pu être importé.',
    'url'                  => ':Attibut format invalide.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'message personnalisé',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
