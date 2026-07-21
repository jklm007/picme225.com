UPDATE users SET mobile = '+225' || mobile WHERE mobile NOT LIKE '+%';
UPDATE providers SET mobile = '+225' || mobile WHERE mobile NOT LIKE '+%';
