
var PublishOnCkanLogic = class {

    constructor() {
        this.ERR_CHARACTERS_UTF_CODE = {
            0x22    : '"',
            0x3A    : ':',
            0x3B    : ';',
            0x3D    : '=',
            0x5F    : '_',
            0xC397  : '×',
            0xC3B7  : '÷'
        };
    };//EndConstructor.

    validateCkanHeader(header) {
        var report = { errors: [], warnings: [] };

        if (typeof header == 'undefined' || header == null) {
            report.errors.push("The dataset has no header.");
            return report;
        }

        if (header.length == 0) {
            report.errors.push("The header of the dataset has no cells.");
            return report;
        }

        for (let i=0; i<header.length; i++) {
            var _cellValue = header[i];
            var _valReport = this.validateCkanHeaderCell(_cellValue, "(1," + (i+1) + ")");
            if (_valReport.errors.length > 0)
                report.errors = report.errors.concat(_valReport.errors);
            if (_valReport.warnings.length > 0)
                report.warnings = report.warnings.concat(_valReport.warnings);
        }//EndForEach Cell.

        return report;
    };//EndFunction.

    validateCkanHeaderCell(value, cellLabel) {
        var errors = [];
        var warnings = [];

        for (let j=0, c; j<value.length && (c = value.charCodeAt(j)) ; j++) {
            if ((typeof this.ERR_CHARACTERS_UTF_CODE[c] === 'undefined') == false)
                errors.push("The cell " + cellLabel + " contains the invalid character <span style=\"color: #F44336; font-weight: 700;\">" + value[j] + "</span>");

            var isInTheRange = (c >= 0xA0 && c <= 0xFF);
            isInTheRange = isInTheRange || (c >= 32 && c <= 126);

            if (!isInTheRange /*&& warnings.length == 0*/) //Considers the cell only one time.
                warnings.push("The cell " + cellLabel + " contains the character <span style=\"color: #FF9800; font-weight: 700;\">" + value[j] + "</span> that CKAN can not display.");
        }//End for each Character.

        return { errors: errors, warnings: warnings };
    };//EndFunction.

};//EndClass.

