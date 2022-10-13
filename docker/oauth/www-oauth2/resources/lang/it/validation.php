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

    'accepted'             => 'Devi accettare :attribute.',
    'active_url'           => ':attribute non è una URL valida.',
    'after'                => ':attribute deve essere una data successiva a :date.',
    'alpha'                => ':attribute può contenere solo lettere.',
    'alpha_dash'           => ':attribute può contenere solo lettere, numeri e trattini (-).',
    'alpha_num'            => ':attribute può contenere solo lettere e numeri.',
    'array'                => ':attribute deve essere un array.',
    'before'               => ':attribute deve essere una data precedente a :date.',
    'between'              => [
        'numeric' => ':attribute deve essere compreso tra :min e :max.',
        'file'    => ':attribute deve essere compreso tra :min e :max kilobyte.',
        'string'  => ':attribute deve essere compreso tra :min e :max caratteri.',
        'array'   => ':attribute deve avere tra :min e :max elementi.',
    ],
    'boolean'              => ':attribute deve essere vero o falso.',
    'confirmed'            => ':attribute e la conferma non corrispondono.',
    'date'                 => ':attribute non è una data valida.',
    'date_format'          => ':attribute deve rispettare il formato :format.',
    'different'            => ':attribute e :other devono essere diversi.',
    'digits'               => ':attribute deve avere :digits cifre.',
    'digits_between'       => ':attribute deve avere tra :min e :max cifre.',
    'dimensions'           => 'Le dimensioni dell\'immagine :attribute non sono valida.',
    'distinct'             => ':attribute ha un valore duplicato.',
    'email'                => ':attribute deve essere un indirizzo e-mail valido.',
    'exists'               => ':attribute non valido.',
    'file'                 => ':attribute deve essere un file.',
    'filled'               => ':attribute è obbligatorio.',
    'image'                => ':attribute deve essere un\'immagine.',
    'in'                   => ':attribute non valido.',
    'in_array'             => ':attribute non presente in :other.',
    'integer'              => ':attribute deve essere un intero.',
    'ip'                   => ':attribute deve essere un indirizzo IP.',
    'json'                 => ':attribute deve essere una stringa JSON.',
    'max'                  => [
        'numeric' => ':attribute non può essere più grande di :max.',
        'file'    => ':attribute non può essere più grande di :max kilobyte.',
        'string'  => ':attribute non può essere più grande di :max caratteri.',
        'array'   => ':attribute non può avere più di :max elementi.',
    ],
    'mimes'                => ':attribute deve essere un file di tipo :values.',
    'mimetypes'            => ':attribute deve essere un file di tipo :values.',
    'min'                  => [
        'numeric' => ':attribute deve essere almeno :min.',
        'file'    => ':attribute deve essere almeno :min kilobyte.',
        'string'  => ':attribute deve essere almeno :min caratteri.',
        'array'   => ':attribute deve avere almeno :min elementi.',
    ],
    'not_in'               => ':attribute non valido.',
    'numeric'              => ':attribute deve essere un numero.',
    'present'              => ':attribute non presente.',
    'regex'                => ':attribute non ha un formato valido.',
    'required'             => ':attribute è obbligatorio.',
    'required_if'          => ':attribute è obbligatorio quando :other is :value.',
    'required_unless'      => ':attribute è obbligatorio a meno che :other sia :values.',
    'required_with'        => ':attribute è obbligatorio quando :values è presente.',
    'required_with_all'    => ':attribute è obbligatorio quando :values sono presenti.',
    'required_without'     => ':attribute è obbligatorio quando :values è mancante.',
    'required_without_all' => ':attribute è obbligatorio quando :values sono mancanti.',
    'same'                 => ':attribute e :other devono corrispondere.',
    'size'                 => [
        'numeric' => ':attribute deve essere :size.',
        'file'    => ':attribute deve essere di :size kilobyte.',
        'string'  => ':attribute deve essere di :size caaratteri.',
        'array'   => ':attribute deve contenere :size elementi.',
    ],
    'string'               => ':attribute deve essere una stringa.',
    'timezone'             => ':attribute deve essere un fusorario.',
    'unique'               => ':attribute non è disponibile.',
    'uploaded'             => 'L\'upload di :attribute non è riuscito.',
    'url'                  => ':attribute non ha un formato valido.',

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
            'rule-name' => 'custom-message',
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
