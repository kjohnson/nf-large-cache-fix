# Ninja Forms - Large Cache Fix

Resolves the issue of large forms crashing on publish due to cache size.

## Methodology

This fix uses the `pre_option_{$option}` and `pre_update_option_{$option}` filters as as a sort of man-in-the-middle solution. Option updates are intercepted on update and "chunked" into multiple options, storing a reference to the created chunks in an additional option. Inversely, option requests are intercepted and joined back together before they are retuned.

## Example
Form Length: 515,988 characters
Chunk Size: 65,535 (TEXT)
Number of Chunks: 8
Number of Options: 10 (chunks + 2)

## MySQL String Type Reference
| Type | Maximum length |
| ----- | ----- |
| TINYTEXT | 255 (2 8−1) bytes |
| TEXT | 65,535 (216−1) bytes = 64 KiB |
| MEDIUMTEXT | 16,777,215 (224−1) bytes = 16 MiB |
| LONGTEXT | 4,294,967,295 (232−1) bytes =  4 GiB |