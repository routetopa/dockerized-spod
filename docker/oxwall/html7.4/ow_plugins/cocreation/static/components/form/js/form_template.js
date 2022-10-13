FORM = {
    formio:null,
    template:{}
};

FORM.init = function()
{
    FORM.create_form();
};

FORM.create_form = function()
{
    parent.$.getJSON(parent.ODE.ajax_coocreation_room_get_array_sheetdata).then((data)=>
    {
        let submission;

        try {
            submission = JSON.parse(parent.COCREATION.form);
        }catch (ex){
            submission = null;
        }

        let components = [];
        let index = 0;
        let e_e;

        for(let key in data[0])
        {
            if(data[0].hasOwnProperty(key))
            {
                let t = parent.$.extend(true, {}, FORM.template.blue_print);

                t.title = key;

                for(let j=0; j<t.components.length; j++) {
                    for (let i = 0; i < t.components[j].columns.length; i++)
                        t.components[j].columns[i].components[0].key = index + '_' + t.components[j].columns[i].components[0].key;
                }

                if(submission && submission[index + '_element-type'] && (e_e = FORM.enrich_element(submission[index + '_element-type'], index)))
                    t.components.push(e_e);

                components.push(t);
                index++;
            }
        }

        // ADD BUTTON
        components.push({type: 'button', action: 'submit', label: 'Submit', theme: 'primary'});

        FORM.render_form(components, submission);
    })
};

FORM.render_form = function (components, submission)
{
    Formio.createForm(document.getElementById('form'), {components: components}).then(function(form)
    {
        FORM.formio = form;

        if(submission) FORM.formio.submission = { data: submission };

        FORM.handle_options();

        FORM.on_formio_submit();

        FORM.on_formio_change();
    });
};

FORM.handle_options = function()
{
    document.querySelectorAll(".accordion").forEach((e)=>{e.style.display = 'none'});

    document.querySelectorAll("button.show-options").forEach((el)=>{
        let is_opened = false;
        el.addEventListener('click', (evt)=>{
            evt.target.closest(".card-body.panel-body").querySelectorAll(".accordion").forEach( (e)=>{
                is_opened ? e.style.display = 'none' : e.style.display = 'block';
            });
            is_opened = !is_opened;
        });
    });
};

FORM.on_formio_change = function ()
{
    FORM.formio.on('change', function(e)
    {
        if(e.changed && e.changed.component.constant_key === 'element-type')
        {
            let index = e.changed.component.key.split('_')[0];

            let el = FormioUtils.getComponent(FORM.formio.component.components, index + '_type_options',true);

            if(el)
            {
                for(let i=0; i<el.columns.length; i++)
                    for(let j=0; j<el.columns[i].components.length; j++)
                        FORM.formio.removeComponentByKey(el.columns[i].components[j].key);

                for(let i=0; i<FORM.formio.component.components.length; i++)
                    if(FORM.formio.component.components[i].key === index + '_type_options') {
                        FORM.formio.component.components.splice(i, 1);
                        break;
                    }
            }

            let op = FORM.enrich_element(e.data[e.changed.component.key], index);

            if(op)
            {
                FORM.formio.addComponent(op, document.querySelectorAll(".card-body.panel-body")[index], FORM.formio.data);
                FORM.formio.component.components.push(op);
            }
        }
    });

};

FORM.enrich_element = function (key, index)
{
    if(key === 'string' || key === 'date_picker' || key === 'province')
        return null;

    let op = parent.$.extend(true, {}, FORM.template[`${key}_options`]);

    for(let i=0; i<op.columns.length; i++)
        op.columns[i].components[0].key = index + '_' + op.columns[i].components[0].key;

    op.key = index + '_' + op.key;

    return op;
};

FORM.on_formio_submit = function ()
{
    FORM.formio.on('submit', (submission) => {
        let items = FORM.parse_submission(submission.data);
        parent.$.post(parent.ODE.ajax_coocreation_room_save_form, { roomId: parent.COCREATION.roomId, form_template: JSON.stringify(submission.data), form: JSON.stringify(items) });
        FORM.formio.setLoading(document.querySelector('button[type="submit"].btn.btn-primary'), false);
        return true;
    });
};

FORM.parse_submission = function(submission)
{
    let items = {};

    for(let key in submission)
    {
        if(submission.hasOwnProperty(key))
        {
            let k = key.split('_');

            if (!items[k[0]])
            {
                items[k[0]] = {key: k[0]};
                parent.$.extend(true, items[k[0]], FORM.template[submission[`${k[0]}_element-type`]]);
            }

            items[k[0]][k[1]] = submission[key];
        }
    }

    return Object.keys(items).reduce((form, elem)=>
    {
        if(items[elem].required) items[elem].validate = {required:true};

        if(items[elem].type === 'number')
        {
            if (items[elem].min) items[elem].validate.min = items[elem].min;
            if (items[elem].max) items[elem].validate.max = items[elem].max;
        }

        if(items[elem].type === 'select' && items[elem].selectValue)
            if(Array.isArray(items[elem].selectValue))
                items[elem].selectValue.forEach((e)=>{items[elem].data.values.push({label:e, value:e})});


        if(items[elem].visible) form.push(items[elem]);

        return form;

    },[]);

};

FORM.init();

// BLUE PRINT

FORM.template.blue_print = {
    title: '',
    theme:'primary',
    type: 'panel',
    components:
        [
            {
                type: 'columns',
                input: false,
                columns:
                    [
                        {
                            width: 5,
                            components : [
                                // LABEL
                                {
                                    key: 'label',
                                    label: 'Label',
                                    type: 'textfield',
                                    input: true,
                                },
                            ]
                        },
                        {
                            width: 5,
                            components : [
                                // TYPE
                                {
                                    key: 'element-type',
                                    constant_key:'element-type',
                                    type: 'select',
                                    label: 'Type',
                                    template: '{{ item.label }}',
                                    multiple: false,
                                    dataSrc: 'values',
                                    input: true,
                                    data: {
                                        values: [
                                            {
                                                label: 'String',
                                                value: 'string',
                                            },
                                            {
                                                label: 'Number',
                                                value: 'number'
                                            },
                                            {
                                                label: 'Date',
                                                value: 'date_picker'
                                            },
                                            {
                                                label: 'Select',
                                                value: 'select'
                                            },
                                            {
                                                label: 'GEO',
                                                value: 'geo'
                                            },
                                            {
                                                label: 'Province',
                                                value: 'province'
                                            },
                                            {
                                                label: 'File',
                                                value: 'file'
                                            }
                                        ]
                                    },
                                    defaultValue : "string",
                                }
                            ]
                        },
                        {
                            width: 1,
                            components : [
                                // VISIBLE
                                {
                                    key: 'visible',
                                    label: 'Visible',
                                    inputType: "checkbox",
                                    type: 'checkbox',
                                    labelPosition: 'top',
                                    input: true,
                                    defaultValue: true
                                },
                            ]
                        },
                        {
                            width: 1,
                            components : [
                                // SHOW OPTIONS
                                {
                                    type: 'button',
                                    theme: 'primary glyphicon glyphicon-plus',
                                    customClass: "show-options",
                                    key: 'show_options'
                                }
                            ]
                        }
                    ]
            },
            // OPTIONS
            {
                type: 'columns',
                input: false,
                customClass: "accordion",
                columns:
                    [
                        {
                            width: 3,
                            components : [
                                // Placeholder
                                {
                                    key: 'placeholder',
                                    label: 'Placeholder',
                                    type: 'textfield',
                                    input: true,
                                },
                            ]
                        },
                        {
                            width: 3,
                            components : [
                                // Tooltip
                                {
                                    key: 'tooltip',
                                    label: 'Tooltip',
                                    type: 'textfield',
                                    input: true,
                                },
                            ]
                        },
                        {
                            width: 3,
                            components : [
                                // Default Value
                                {
                                    key: 'defaultValue',
                                    label: 'Default',
                                    type: 'textfield',
                                    input: true,
                                },
                            ]
                        },
                        {
                            width: 2,
                            components : [
                                // Description
                                {
                                    key: 'description',
                                    label: 'Description',
                                    type: 'textfield',
                                    input: true,
                                }
                            ]
                        },
                        {
                            width: 1,
                            components : [
                                // VISIBLE
                                {
                                    key: 'required',
                                    label: 'Required',
                                    inputType: "checkbox",
                                    type: 'checkbox',
                                    labelPosition: 'top',
                                    input: true,
                                    defaultValue: true
                                },
                            ]
                        }
                    ]
            }
        ]
};

// ELEMENT TEMPLATE

FORM.template.date_picker = {
    type: 'datetime',
    input: true,
    format: 'yyyy-MM-dd hh:mm a',
    enableDate: true,
    enableTime: true,
    defaultDate: '',
    datepickerMode: 'day',
    datePicker: {
        showWeeks: true,
        startingDay: 0,
        initDate: '',
        minMode: 'day',
        maxMode: 'year',
        yearRows: 4,
        yearColumns: 5,
        datepickerMode: 'day'
    },
    timePicker: {
        hourStep: 1,
        minuteStep: 1,
        showMeridian: true,
        readonlyInput: false,
        mousewheel: true,
        arrowkeys: true
    }
};

FORM.template.string = {
    type: 'textfield',
    input: true
};

FORM.template.number = {
    type: 'number',
    input: true,
    "validate": {}
};

FORM.template.select = {
    "type": "select",
    "template": '{{ item.label }}',
    "input": true,
    "dataSrc": 'values',
    "data": {
        "values": []
    },
    "valueProperty": "value"
};

FORM.template.province = {
    type: 'select',
    template: '{{ item.label }}',
    multiple: false,
    dataSrc: 'values',
    input: true,
    data: {
        values: [
            {
                label: 'Benevento',
                value: 'Benevento',
            },
            {
                label: 'Caserta',
                value: 'Caserta'
            },
            {
                label: 'Napoli',
                value: 'Napoli'
            },
            {
                label: 'Salerno',
                value: 'Salerno'
            }
        ]
    }
};

FORM.template.file = {
    type: "file",
    input: true,
    filePattern: '',
    fileMinSize: '',
    fileMaxSize: '10MB',
    storage: 'Base64'
};

// TYPE OPTIONS

FORM.template.geo_options = {
    type: 'columns',
    input: false,
    columns: [
        {
            width: 3,
            components : [
                // GEO
                {
                    key: 'geo',
                    label: 'geo',
                    type: 'number',
                    input: true,
                }
            ]
        }
    ],
    key: 'type_options'
};

FORM.template.number_options = {
    type: 'columns',
    input: false,
    columns: [
        {
            width: 3,
            components : [
                // Min
                {
                    key: 'min',
                    label: 'Minimo',
                    type: 'number',
                    input: true,
                }
            ]
        },
        {
            width: 3,
            components : [
                // Max
                {
                    key: 'max',
                    label: 'Massimo',
                    type: 'number',
                    input: true,
                }
            ]
        }
    ],
    key: 'type_options'
};

FORM.template.select_options = {
    type: 'columns',
    input: false,
    columns: [
        {
            width: 3,
            components : [
                // Value
                {
                    key: 'selectValue',
                    label: 'Valori',
                    type: 'textfield',
                    input: true,
                    multiple: true,
                }
            ]
        }
    ],
    key: 'type_options'
};

FORM.template.file_options =
{
    type: 'columns',
    input: false,
    columns: [
        {
            width: 3,
            components: [
                {
                    key: 'filePattern',
                    type: 'select',
                    template: '{{ item.label }}',
                    multiple: false,
                    dataSrc: 'values',
                    input: true,
                    data: {
                        values: [
                            {
                                label: 'Immagini',
                                value: '.jpg,.png,.giff',
                            },
                            {
                                label: 'Documenti',
                                value: '.pdf,.doc,.word'
                            }
                        ]
                    }
                }
            ]
        }
    ],
    key: 'type_options'
};