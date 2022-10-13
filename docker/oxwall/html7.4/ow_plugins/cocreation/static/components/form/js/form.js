FORM = {
    formio:null
};

FORM.init = function()
{
    FORM.create_form();
};

FORM.create_form = function()
{
    let components = JSON.parse(parent.COCREATION.form);
    components.push({type: 'button', action: 'submit', label: 'Submit', theme: 'primary'});

    Formio.createForm(document.getElementById('form'), {components: components}).then(function(form)
    {
        form.fileService = '......';
        FORM.formio = form;

        FORM.formio.on('submit', (submission) =>
        {
            parent.$.post(parent.ODE.ajax_coocreation_room_save_form, { roomId: parent.COCREATION.roomId, submission: JSON.stringify(submission.data), sheet_name: parent.COCREATION.sheetName });
            FORM.formio.setLoading(document.querySelector('button[type="submit"].btn.btn-primary'), false);
            return true;
        });
    });
};

FORM.init();