icons.yml
```yml
---
Name: app-icons
---
Goldfinch\Icon\Forms\IconFileField:
  icon_folder: 'assets/icons'

Goldfinch\Icon\Forms\IconFontField:
  icon_fonts:
    - 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css'
  icon_list:
    - bi-box-seam-fill
    - bi-bricks
    - bi-bug-fill
    - bi-earbuds
    - bi-duffle-fill
    # bi-box-seam-fill: bi-box-seam-fill
    # bi-bricks: bi-bricks
    # bi-bug-fill: bi-bug-fill
    # bi-earbuds: bi-earbuds
    # bi-duffle-fill: bi-duffle-fill
```



```yml
Goldfinch\Icon\Forms\IconField:
  icons_sets:
    # set_a:
      # type: font # font | dir | upload | json
      # source: "https://*" # link | dir | assets_dir | path
      # schema: "*.json"
      # multiple: false # true | false | numeric
      # search: true
      # search_show: 10
      # include: "*"
      # exclude: "*"
      # icon size # (for admin view)
      # icon color # (for admin view)
    set_a:
      type: font
      source: "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css"
    set_b:
      type: dir
      dir_save_rule: name # name | filename | full path
      source: "/assets/icons"
    set_c:
      type: upload
      # allowed_extension:
      #   - svg # png etc.
      source: "icons"
```
