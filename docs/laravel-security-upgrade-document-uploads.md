• Replace mimes:jpeg,jpg,png,bmp,pdf,doc,docx with mimetypes: lists (image/jpeg,image/png,image/bmp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document) everywhere
• For LeaveController: append text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; map 'docs' typo to docx MIME
• Inspect upload handlers: if filenames built from getClientOriginalName() or raw request values, switch to timestamp prefix + Str::slug(stable identifier) + guessExtension() generated name
• Keep exact same required/nullable/sometimes|file|max:5120 prefix and all messages
• Do not touch commented-out code
• Run php -l on every changed file and php artisan test to confirm nothing broke