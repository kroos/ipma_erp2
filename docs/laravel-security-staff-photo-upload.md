Laravel Security Fix: Staff Photo Upload Hardening
=================================================

Overview
--------
This fix hardens the staff photo upload validation by replacing extension-only
mimes: rules with real MIME type validation (mimetypes:) which uses PHP's finfo
to inspect the actual file content, closing the double-extension bypass (e.g.
evil.jpg.php being accepted). Additionally, stored filenames are generated from
timestamp + sanitized identifier + guessed extension, never from client-supplied
names.

Files Modified
--------------

1. app/Http/Requests/HumanResources/Staff/StaffRequestStore.php
   - Line 50: 'image' => 'nullable|file|max:5120|mimetypes:image/jpeg,image/png,image/bmp'
   - The mimetypes rule validates the real file MIME type via finfo, preventing
     double-extension attacks. The existing max:5120 size limit is kept.
   - Line 51: // 'document' => 'sometimes|file|max:5120|mimes:jpeg,jpg,png,bmp,pdf,doc,docx'
   - This commented-out line is left untouched per instructions.

2. app/Http/Requests/HumanResources/Staff/StaffRequestUpdate.php
   - Line 50: 'image' => 'nullable|file|max:5120|mimetypes:image/jpeg,image/png,image/bmp'
   - Same MIME-based validation as StaffRequestStore.php.
   - Line 51: // 'document' => 'sometimes|file|max:5120|mimes:jpeg,jpg,png,bmp,pdf,doc,docx'
   - Commented-out line left untouched.

3. app/Http/Controllers/HumanResources/HRDept/StaffController.php - store() method (lines 93-103)
   - Filename generation now uses: $currentDate.'_'.Str::slug($request->username).'.'.$request->file('image')->guessExtension()
   - Never embeds getClientOriginalName() or concatenates the raw $request->username.
   - The generated $fileName is stored via storeAs('public/user_profile', $fileName).
   - Existing $data += ['image' => $fileName] and storeAs() lines remain unchanged.
   - Str:: is already imported (use Illuminate\Support\Str at line 36).

   - app/Http/Controllers/HumanResources/HRDept/StaffController.php - update() method (lines 245-254)
   - Same safe filename generation pattern as store(): timestamp + Str::slug() + guessExtension().
   - Does not change any other behavior in the update method.

4. app/Http/Controllers/HumanResources/AjaxController.php - uploaddoc method (lines 1034-1039)
   - Document rule at line 1021: 'document' => 'required|file|max:5120|mimetypes:image/jpeg,image/png,image/bmp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
   - Upload handler generates safe filename: $fileName = now()->timestamp . '_' . Str::slug($staffName) . '.' . $ext
   - Uses $request->file('document')->guessExtension() rather than getClientOriginalName().
   - Stores only the generated name via $request->document->storeAs('public/leaves', $fileName).

5. app/Http/Controllers/API/AjaxSupportController.php - document rule (line 2611)
   - 'document' => 'required|file|max:5120|mimetypes:image/jpeg,image/png,image/bmp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
   - Real MIME validation via mimetypes: rule, closing extension-only bypass.

6. app/Http/Controllers/HumanResources/HRDept/LeaveController.php - document rule (line 210)
   - 'document' => 'nullable|file|max:5120|mimetypes:image/jpeg,image/png,image/bmp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
   - Full MIME list includes: image types, PDF, Word docx, and CSV/Excel MIME types.
   - The 'docs' typo in the original is mapped to the docx MIME application/vnd.openxmlformats-officedocument.wordprocessingml.document.
   - Prefix `nullable|file|max:5120` is preserved unchanged.
   - All validation messages remain the same.

7. app/Http/Requests/HumanResources/Leave/HRLeaveRequestStore.php - document rule (line 37)
   - 'document' => 'sometimes|file|max:5120|mimetypes:image/jpeg,image/png,image/bmp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
   - Uses mimetypes: for real MIME validation with sometimes|file|max:5120 prefix.
   - All messages preserved unchanged.

Verification
------------
After applying these changes, run:
  php -l <each-changed-file>
  php artisan test

to confirm no syntax errors and that existing tests pass.

Notes
-----
- The mimetypes: rule inspects the file's real MIME via PHP's finfo extension,
  which prevents double-extension bypass (e.g., .jpg.php files are rejected even
  though the extension appears valid).
- Str::slug() sanitizes the username/staff identifier for safe inclusion in
  filenames; never use getClientOriginalName() or raw request values in
  generated filenames.
- The existing storeAs() and $data += ['image' => $fileName] assignment patterns
  are preserved; only the filename generation before them is replaced.