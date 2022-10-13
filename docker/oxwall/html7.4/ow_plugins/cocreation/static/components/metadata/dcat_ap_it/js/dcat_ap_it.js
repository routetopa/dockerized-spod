METADATA = {
    form:null,
    components:null
};

METADATA.init = function() {
    METADATA.create_form();
};

METADATA.realtime_metadata = function (data) {
    METADATA.form.submission = {
        data: JSON.parse(data)
    };
};

METADATA.loadTheme = function(theme) {
    if(!theme) return;
    return THEMES[theme];
};

METADATA.setMetadata = function(metadata) {
    try {
        parent.COCREATION.metadata = METADATA.parseMetadata(metadata);

        METADATA.form.submission = {
            data: parent.COCREATION.metadata
        };
    } catch (e) {
        console.log('incompatible metadata - impossible to import metadata', e);
    }
};

METADATA.parseMetadata = function(metadata) {
    let themes = JSON.parse(metadata.theme);
    let formio_themes = [];

    for (let i in themes) {
        formio_themes.push(
            {
                "dcat_theme":{value: themes[i].theme, label: METADATA.getLabelbyValue(themes[i].theme, THEMES['main_theme_' + parent.ODE.user_language])},
                "dct_subject":[]
            }
        );
    }

    //todo get resources index
    return meta = {
        "dct_title": metadata.resources[0].name,
        "dct_description": metadata.resources[0].description,
        "dct_identifier": metadata.identifier,
        "dct_language":[],
        "dct_license":"",
        "owl_versionInfo":"",
        "dcat_theme-dct_subject": formio_themes,
        "dcat_keyword":"",
        "foaf_agent-dct_identifier-foaf_agent-foaf_name":[{"foaf_agent-foaf_name":"","foaf_agent-dct_identifier":""}],
        "dct_issued":"",
        "dct_modified": metadata.modified,
        "dct_accrualPeriodicity":{"value": metadata.frequency, "label": METADATA.getLabelbyValue(metadata.frequency, FREQUENCY['frequency_' + parent.ODE.user_language])},
        "dct_temporal":[
            {"dct_period_of_time-schema_start_date":"",
                "dct_period_of_time-schema_end_date":""}
        ],
        "dct_spatial":[],
        "locn_geographicalName":"",
        "dcatapit_geographicalIdentifier":"",
        "dct_conformsTo":[{"dct_standards-dct_identifier":"","dct_standards-dct_title":"","dct_standards-dct_description":"","dct_standards-referenceDocumentation_URI":""}],
        "adms_identifier":[{"othid_identifier":"","othid_organization_name":"","othid_organization_identifier":""}],
        "dct_creator":"",
        "dct_publisher":"",
        "dct_rights_holder":"",
        "dcat_contactPoint":"",
        "distribution_dct_title":"",
        "distribution_dct_description":"",
        "dct_format":"",
        "dcat_byteSize":""
    };
};

METADATA.getLabelbyValue = function (val, dictionary) {
    return dictionary.filter((o)=>{return o.value === val})[0].label;
};

METADATA.create_form = function() {
    METADATA.components = METADATA.getComponents();

    METADATA.add_info(METADATA.components);

    let is_read_only = !!window.frameElement.getAttribute('data-read-only');

    Formio.createForm(document.getElementById('dcat_ap_it_form'),
        { components: METADATA.components }, { readOnly: is_read_only }
    ).then(function(form)
    {
        METADATA.form = form;

        let meta = this.parent.COCREATION.metadata ? (typeof this.parent.COCREATION.metadata === 'string' ? JSON.parse(this.parent.COCREATION.metadata) : this.parent.COCREATION.metadata) : null;

        if(meta)
        {
            METADATA.form.submission = {
                data: meta
            };
        }

        METADATA.form.on('submit', (submission) => {
            this.parent.window.dispatchEvent(new CustomEvent('update-metadata', {detail: { metadata: submission.data} }));
        });

        document.getElementById("tmp-submit").addEventListener('click', () => {

            //let isValid = METADATA.form.checkValidity(METADATA.form.submission.data); //The right one ... to sobstitute when formio will fix the tab bug
            let isValid = METADATA.checkValidity(METADATA.components, METADATA.form.submission.data);

            if (isValid) {
                METADATA.form.submit();
            } else {
                METADATA.form.setAlert('danger', 'Campi obbligatori mancanti');
            }
        });
    });
};

METADATA.checkValidity = function(form, data) {
    for(let i in form)
    {
        if(Array.isArray(form[i])) {
            return METADATA.checkValidity(form[i], data);
        } else if (form[i].components) {
            return METADATA.checkValidity(form[i].components, data);
        } else {
            if(form[i].validate && form[i].validate.required ) {
                console.log(form[i].key);
                if(!data[form[i].key] || data[form[i]] === "")
                    return false;
            }
        }
    }

    return true;
};

METADATA.add_info = function(components) {
    let ln = parent.ODE.user_language || 'en';

    components.forEach((e)=>
    {
        if(e.components || e.columns)
            METADATA.add_info(e.components || e.columns);

        if(e.key && dcat_ap_it_ln[e.key + '-label-' + ln])
        {
            e.label       = dcat_ap_it_ln[e.key + '-label-' + ln];
            e.placeholder = dcat_ap_it_ln[e.key + '-placeholder-' + ln];
            e.tooltip     = dcat_ap_it_ln[e.key + '-tooltip-' + ln];
        } else if(dcat_ap_it_ln[e.key + '-title-' + ln])
            e.title = dcat_ap_it_ln[e.key + '-title-' + ln];
    })

};

METADATA.getComponents = function() {
    return [
        // TABS
        {
            type: "tabs",
            components: [
                {
                    key: "dct_tab1",
                    label: "Informazioni",
                    components: METADATA.getTab1Components()
                },
                {
                    key: "dct_tab2",
                    label: "Classificazione",
                    components: METADATA.getTab2Components()
                },
                {
                    key: "dct_tab3",
                    label: "Organizzazioni",
                    components: METADATA.getTab3Components()
                },
                {
                    key: "dct_tab4",
                    label: "Riferimenti Temporali",
                    components: METADATA.getTab4Components()
                },
                {
                    key: "dct_tab5",
                    label: "Riferimenti Spaziali",
                    components: METADATA.getTab5Components()
                },
                {
                    key: "dct_tab6",
                    label: "Standards",
                    components: METADATA.getTab6Components()
                },
                {
                    key: "dct_tab7",
                    label: "Datasets Collegati",
                    components: METADATA.getTab7Components()
                },
                {
                    key: "dct_tab8",
                    label: "Informazioni supplementari",
                    components: METADATA.getTab8Components()
                }
            ]
        },

        // SUBMIT
        /*{
            type: 'button',
            label: 'Salva',
            action: 'submit',
            theme: 'primary'
        }*/
    ]
};

METADATA.getTab1Components = function() {
    return [
        // TITLE
        {
            key: 'dct_title',
            type: 'textfield',
            defaultValue: undefined,
            validate: { required: true }
        },

        // DESCRIPTION
        {
            key: 'dct_description',
            type: 'textarea',
            validate: { required: true }
        },

        // IDENTIFIER
        {
            key: 'dct_identifier',
            type: 'textfield',
            validate: { required: true }
        },

        // LANGUAGE
        {
            key: "dct_language",
            type: "select",
            data: {
                values: [
                    {
                        value: "it",
                        label: "Italiano"
                    },
                    {
                        value: "en",
                        label: "English"
                    },
                    {
                        value: "fr",
                        label: "Français"
                    },
                    {
                        value: "de",
                        label: "Deutsch"
                    },
                    {
                        value: "ne",
                        label: "Nederlands"
                    }
                ]
            },
            dataSrc: "values",
            multiple: true
        },

        // LICENSE
        {
            key: 'dct_license',
            type: 'select',
            data: {
                custom: "values =  LICENSE.license_" + parent.ODE.user_language
            },
            dataSrc: "custom",
            multiple: false
        },

        //todo VISIBILITA

        // VERSION
        {
            key: 'owl_versionInfo',
            type: 'textfield'
        }
    ]
};

METADATA.getTab2Components = function() {
    return [
        // THEME
        {
            key: 'dcat_theme-dct_subject',
            type: 'datagrid',
            components: [
                {
                    key: "dcat_theme",
                    type: "select",
                    data: {
                        custom: "values = METADATA.loadTheme('main_theme_' + parent.ODE.user_language)"
                    },
                    dataSrc: "custom",
                    validate: { required: true }
                },
                {
                    key: "dct_subject",
                    type: "select",
                    data: {
                        custom: "values = METADATA.loadTheme(row.dcat_theme.value + '_' + parent.ODE.user_language)"
                    },
                    dataSrc: "custom",
                    refreshOn: 'dcat_theme',
                    multiple: true
                }
            ]
        },

        // KEYWORD
        {
            key: 'dcat_keyword',
            type: 'textfield'
        }
    ]
};

METADATA.getTab3Components = function() {
    return [
        // CREATOR
        {
            key: 'foaf_agent-dct_identifier-foaf_agent-foaf_name',
            type: 'datagrid',
            components: [
                {
                    key: 'foaf_agent-foaf_name',
                    type: 'textfield'
                },
                {
                    key: 'foaf_agent-dct_identifier',
                    type: 'textfield'
                }
            ]
        }
    ]
};

METADATA.getTab4Components = function() {
    return [
        // ISSUED DATE
        {
            key: 'dct_issued',
            type: 'datetime',
            format: 'dd/MM/yyyy',
            enableTime: false
        },

        // MODIFIED DATE
        {
            key: 'dct_modified',
            type: 'datetime',
            format: 'dd/MM/yyyy',
            enableTime: false,
            validate: { required: true }
        },

        // ACCRUAL PERIODICITY
        {
            key: 'dct_accrualPeriodicity',
            type: 'select',
            data: {
                custom: "values = FREQUENCY.frequency_" + parent.ODE.user_language
            },
            dataSrc: "custom",
            multiple: false,
            validate: { required: true }
        },

        // PERIOD OF TIME - (INIZIO - FINE)
        {
            key: 'dct_temporal',
            type: 'datagrid',
            components: [
                {
                    key: 'dct_period_of_time-schema_start_date',
                    type: 'datetime',
                    format: 'dd/MM/yyyy',
                    enableTime: false
                },
                {
                    key: 'dct_period_of_time-schema_end_date',
                    type: 'datetime',
                    format: 'dd/MM/yyyy',
                    enableTime: false
                }
            ]
        }
    ]
};

METADATA.getTab5Components = function() {
    return [
        // SPATIAL
        {
            key: "dct_spatial",
            type: "select",
            data: {
                custom : "values = COVERAGE.coverage_" + parent.ODE.user_language
            },
            dataSrc: "custom",
            multiple: true
        },

        // Geographical Name
        {
            key: 'columns',
            type: 'columns',
            columns: [
                {
                    components: [
                        {
                            key: 'locn_geographicalName',
                            type: 'textfield'
                        }
                    ]
                },
                {
                    components: [
                        {
                            key: 'dcatapit_geographicalIdentifier',
                            type: 'textfield'
                        }
                    ]
                }
            ]
        }
    ]
};

METADATA.getTab6Components = function() {
    return [
        // CONFORMS TO (IDENTIFICATORE - TITOLO - DESCRIZIONE - URI)
        {
            key: 'dct_conformsTo',
            type: 'datagrid',
            components: [
            {
                key: 'dct_standards-dct_identifier',
                type: 'textfield'
            }, {
                key: 'dct_standards-dct_title',
                type: 'textfield'
            }, {
                key: 'dct_standards-dct_description',
                type: 'textfield'
            },
            {
                key: 'dct_standards-referenceDocumentation_URI',
                type: 'textfield'
            }]
        }
    ]
};

METADATA.getTab7Components = function() {
    return [
        // OTHER IDENTIFIER
        {
            key: 'adms_identifier',
            type: 'datagrid',
            components: [
            {
                key: 'othid_identifier',
                type: 'textfield'
            },
            {
                key: 'othid_organization_name',
                type: 'textfield'
            },
            {
                key: 'othid_organization_identifier',
                type: 'textfield'
            }]
        }
    ]
};

METADATA.getTab8Components = function() {
    return [
        // AUTHOR
        {
            key: 'dct_creator',
            type: 'textfield'
        },

        // PUBLISHER
        {
            key: 'dct_publisher',
            type: 'textfield'
        },

        // RIGHTS HOLDER
        {
            key: 'dct_rights_holder',
            type: 'textfield'
        },

        // CONTACT POINT
        {
            key: 'dcat_contactPoint',
            type: 'textfield'
        },

        // DISTRIBUTION TITLE
        {
            key: 'distribution_dct_title',
            type: 'textfield'
        },

        // DISTRIBUTION DESCRIPTION
        {
            key: 'distribution_dct_description',
            type: 'textarea'
        },

        // DISTRIBUTION FORMAT
        {
            key: 'dct_format',
            type: 'select',
            data: {
                values: [
                    {
                        label: 'CSV',
                        value: 'csv'
                    },
                    {
                        label: 'XML',
                        value: 'xml'
                    }
                ]
            },
            dataSrc: 'values'
        },

        // BYTE SIZE
        {
            key: 'dcat_byteSize',
            type: 'textfield'
        }
    ]
};

METADATA.init();