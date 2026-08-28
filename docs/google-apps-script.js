/**
 * Google Apps Script Web App — receives form submissions from the Angel
 * Investor backend (see App\Services\GoogleSheetsService::push()) and syncs
 * them into this spreadsheet, one tab per form.
 *
 * Not deployed from this repo — Apps Script only lives inside the target
 * Google account. This file is kept here purely as a reference copy of
 * what's pasted into script.google.com. See docs/google-apps-script-setup.md
 * for the exact deployment steps.
 *
 * Behavior:
 *  - Creates the tab (sheet) named `tab` if it doesn't exist yet.
 *  - On first write, creates a header row from the keys of `data`.
 *  - On later writes, any new key not yet in the header row is appended as
 *    a new column automatically — so a form field added later in Laravel
 *    doesn't require any manual spreadsheet edits.
 *  - Values are matched to existing columns by header name (not position),
 *    so column order in the sheet can be freely rearranged by hand.
 *  - Any value starting with +, =, -, or @ is prefixed with a leading
 *    apostrophe before being written, so Sheets doesn't misinterpret e.g.
 *    a phone number "+923001234567" as a formula (CSV/formula-injection
 *    guard — same fix already proven on the faithfuture Volunteer form).
 */
function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.waitLock(10000);

  try {
    var body = JSON.parse(e.postData.contents);
    var tabName = body.tab;
    var data = body.data || {};

    var ss = SpreadsheetApp.openById('1odk5frybbLODyGjNFtNrq-8om1bfg6AjkC3KJkfm_5s');
    var sheet = ss.getSheetByName(tabName);
    if (!sheet) {
      sheet = ss.insertSheet(tabName);
    }

    var lastCol = sheet.getLastColumn();
    var headers = lastCol > 0 ? sheet.getRange(1, 1, 1, lastCol).getValues()[0] : [];

    var keys = Object.keys(data);
    var newHeaders = headers.slice();
    keys.forEach(function (key) {
      if (newHeaders.indexOf(key) === -1) {
        newHeaders.push(key);
      }
    });

    if (newHeaders.length !== headers.length) {
      sheet.getRange(1, 1, 1, newHeaders.length).setValues([newHeaders]);
      headers = newHeaders;
    }

    var row = headers.map(function (header) {
      var value = data[header];
      if (value === undefined || value === null) return '';
      var str = String(value);
      if (/^[+=\-@]/.test(str)) {
        return "'" + str;
      }
      return str;
    });

    sheet.appendRow(row);

    return ContentService.createTextOutput(JSON.stringify({ success: true }))
      .setMimeType(ContentService.MimeType.JSON);
  } catch (err) {
    return ContentService.createTextOutput(JSON.stringify({ success: false, error: String(err) }))
      .setMimeType(ContentService.MimeType.JSON);
  } finally {
    lock.releaseLock();
  }
}
