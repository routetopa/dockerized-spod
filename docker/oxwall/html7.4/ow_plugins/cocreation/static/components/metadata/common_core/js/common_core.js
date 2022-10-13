METADATA = {
    form:null
};

METADATA.init = function()
{
    METADATA.create_form();
};

METADATA.realtime_metadata = function (data)
{
    METADATA.form.submission = {
        data: JSON.parse(data)
    };
};

METADATA.create_form = function()
{
    let components = [
        // TITOLO
        {
            type: 'textfield',
            key: 'title',
            input: true
        },

        // DESCRIPTION
        {
            type: 'textfield',
            key: 'description',
            input: true
        },

        // LICENSE
        {
            type: "select",
            key: "license",
            data: {
                values: [
                    {value:"notspecified", label:"License not specified"},
                    {value:"cc-by", label:"Creative Commons Attribution"},
                    {value:"cc-by-sa", label:"Creative Commons Attribution Share-Alike"},
                    {value:"cc-zero", label:"Creative Commons CCZero"},
                    {value:"cc-nc", label:"Creative Commons Non-Commercial, (Any)"},
                    {value:"gfdl", label:"GNU Free Documentation License"},
                    {value:"odc-by", label:"Open Data Commons Attribution License"},
                    {value:"odc-odbl", label:"Open Data Commons Open Database License (ODbL)"},
                    {value:"odc-pddl", label:"Open Data Commons Public Domain Dedication and License (PDDL)"},
                    {value:"other-at", label:"Other (Attribution)"},
                    {value:"other-nc", label:"Other (Non-Commercial)"},
                    {value:"other-closed", label:"Other (Not Open)"},
                    {value:"other-open", label:"Other (Open)"},
                    {value:"other-pd", label:"Other (Public Domain)"},
                    {value:"uk-ogl", label:"UK Open Government Licence (OGL)"}
                ]
            },
            dataSrc: "values",
            template: "<span>{{ item.label }}</span>",
            multiple: false,
            input: true
        },


        // LANGUAGE
        {
            type: "select",
            key: "language",
            data: {
                values: [
                    {value:"it", label:"Italiano"},
                    {value:"en", label:"English"},
                    {value:"es", label:"Español"},
                    {value:"fr", label:"Français"},
                    {value:"de", label:"Deutsch"},
                    {value:"pl", label:"Polski"},
                    {value:"nl", label:"Nederlan"}
                ]
            },
            dataSrc: "values",
            template: "<span>{{ item.label }}</span>",
            multiple: false,
            input: true
        },

        // VERSION
        {
            type: 'textfield',
            key: 'version',
            input: true
        },

        // CONTACT NAME
        {
            type: 'textfield',
            key: 'contact_name',
            input: true
        },

        // CONTACT E-MAIL
        {
            type: 'textfield',
            key: 'contact_email',
            input: true
        },

        // MAINTAINER
        {
            type: 'textfield',
            key: 'maintainer',
            input: true
        },

        // MAINTAINER E-MAIL
        {
            type: 'textfield',
            key: 'maintainer_email',
            input: true
        },

        // ORIGIN
        {
            type: 'textfield',
            key: 'origin',
            input: true
        },

        // "Common Core" Required Fields
        {
            input:false,
            theme:'primary',
            key: 'common_core_required_fields',
            type: 'panel',
            components: [
                {
                    type: 'textfield',
                    key: 'tags',
                    input: true
                },
                {
                    type: 'datetime',
                    key: 'last_update',
                    datepickerMode: 'day',
                    enableDate: true,
                    enableTime: false,
                    format: 'dd-MM-yyyy',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'publisher',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'unique_identifier',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'public_access_level',
                    input: true
                }
            ]
        },

        // "Common Core" Required if Applicable Fields
        {
            input: false,
            theme: 'primary',
            type: 'panel',
            key: 'common_core_if_applicable_fields',
            components: [
                {
                    type: 'textfield',
                    key: 'bureau_code',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'program_code',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'access_level_comment',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'download_url',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'endpoint',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'format',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'spatial',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'temporal',
                    input: true
                }
            ]
        },

        // Expanded Fields
        {
            input: false,
            theme: 'primary',
            type: 'panel',
            title: 'Expanded Fields',
            components: [
                {
                    type: 'textfield',
                    key: 'category',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'data_dictionary',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'data_quality',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'distribution',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'frequency',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'homepage_url',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'primary_it_investment_uii',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'related_documents',
                    input: true
                },
                {
                    type: 'datetime',
                    key: 'release_date',
                    datepickerMode: 'day',
                    enableDate: true,
                    enableTime: false,
                    format: 'dd-MM-yyyy',
                    input: true
                },
                {
                    type: 'textfield',
                    key: 'system_of_records',
                    input: true
                },
            ]
        },
        {
            type: 'button',
            action: 'submit',
            label: 'Submit',
            theme: 'primary'
        }];

    METADATA.add_info(components);

    Formio.createForm(document.getElementById('common_core_form'), {
        components: components
    }).then(function(form)
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

        // Everytime the form changes, this will fire.
        METADATA.form.on('change', function(changed) {
            console.log('Form was changed', changed);
        });

    });
};

METADATA.add_info = function(components)
{
    let ln = parent.ODE.user_language || 'en';

    components.forEach((e)=>
    {
        if(e.components || e.columns)
            METADATA.add_info(e.components || e.columns);

        if(e.key && common_core_ln[e.key + '-label-' + ln])
        {
            e.label       = common_core_ln[e.key + '-label-' + ln];
            e.placeholder = common_core_ln[e.key + '-placeholder-' + ln];
            e.tooltip     = common_core_ln[e.key + '-tooltip-' + ln];
        } else if(common_core_ln[e.key + '-title-' + ln])
            e.title = common_core_ln[e.key + '-title-' + ln];
    })

};

METADATA.init();