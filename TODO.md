# Task: Show customer email replies in admin panel CRM email history

## Steps
- [x] 1. Analyze the issue - identified that AdminCustomerReports.vue lacks email history UI
- [x] 2. Research all relevant files (backend + frontend) to understand data flow
- [x] 3. Create plan and get user approval
- [x] 4. Update AdminCustomerReports.vue:
  - [x] 4a. Add email history state variables (emailHistoryCache, loadingEmails, expandedEmailHistory, showEmailModal, emailForm, sendingEmail)
  - [x] 4b. Add functions: loadEmailHistory(), toggleEmailHistory(), getEmailHistory(), openEmailModal(), closeEmailModal(), sendEmail()
  - [x] 4c. Add email history UI section inside the edit modal
  - [x] 4d. Add "Send Email" button in modal footer
  - [x] 4e. Add CSS styles for email history elements
- [x] 5. Verify changes

