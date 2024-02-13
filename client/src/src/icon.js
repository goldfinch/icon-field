(function ($) {

  function setIconValue(val, input, selected, parent) {
    input.val(val)

    let vals = val.split(',')
    selected.html('')

    let ul = $('<ul>');
    vals.forEach((i, li) => {
      ul.append('<li data-key="'+i+'"><i class="'+i+'" title="'+i+'"></i></li>')

      parent.find('li[data-key="'+i+'"]').attr('data-selected', true)
    })
    selected.html(ul)
  }

  function filterSelection(e, el) {

    clearTimeout(window.iconfilterSelectionTimeout)

    window.iconfilterSelectionTimeout = setTimeout(() => {

      let els = el.children('ul').children('li');
      let span = el.children('span');
      let searchStr = e.target.value;

      if (searchStr == '') {
        el.attr('data-search', false)
        span.html('')
        els.each((i, li) => {
          li.setAttribute('data-display', true)
        })
      } else {
        el.attr('data-search', true)

        els.each((i, li) => {
          // let v = $(li);
          if (li.getAttribute('data-key').search(searchStr) >= 0) {
            li.setAttribute('data-display', true)
          } else {
            li.setAttribute('data-display', false)
          }
        })

        let foundEls = el.children('ul').children('li[data-display="true"]');

        span.html(foundEls.length + ' icons found')

      }

    }, 500)
  }

  function initSelections(source, el, input, selected) {
    let ul = $('<ul>');
    let span = $('<span>');
    let vals = [];

    if (input && input.length) {
      vals = input[0].value.split(',')
    }

    for (const [key, value] of Object.entries(source)) {
      ul.append('<li data-key="'+key+'" data-selected="'+(vals.includes(key) ? true : false)+'"><label><i class="'+key+'" title="'+(value.title ? value.title : key)+'"></i></label></li>')
    }

    el.append(span)
    el.append(ul)


    el.find('li > label').off('click').on('click', (e) => {

      el.find('li').attr('data-selected', false) // diselect all

      setIconValue($(e.currentTarget).closest('li').attr('data-key'), input, selected, el)
    });
  }

  $(document).ready(() => {
    //
  });

  $('.cms-edit-form').entwine({
    onmatch(e) {
      this._super(e);
    },
    onunmatch(e) {
      this._super(e);
    },
    onaftersubmitform(event, data) {
      // ..
    },
  });

  $.entwine('ss', ($) => {
    $('[data-goldfinch-icon-field]').entwine({
      onmatch() {
        const config = JSON.parse($(this).attr('data-goldfinch-icon-config'));
        const source = JSON.parse($(this).attr('data-goldfinch-icon-source'));

        const input = $(this).find('[data-goldfinch-icon-input]');
        const selected = $(this).find('[data-goldfinch-icon-selected]');
        const loader = $(this).find('[data-goldfinch-icon-loader]')[0];
        const loaderBtn = $(this).find('[data-goldfinch-icon-loader] button')[0];
        const searchBox = $(this).find('[data-goldfinch-icon-search]');
        const selection = $(this).find('[data-goldfinch-icon-selection]');

        $(loaderBtn).on('click', () => {

          loader.remove()
          searchBox.removeClass('goldfinchicon__hide')
          searchBox.on('keydown', (e) => {
            filterSelection(e, selection)
          })
          initSelections(source, selection, input, selected)
          selection.removeClass('goldfinchicon__hide')
        })
        // ..
      },
    });
  });
})(jQuery);



// document
//   .querySelectorAll('.js-goldfinchicon input[type=radio]')
//   .forEach((elem) => {
//     elem.addEventListener('click', allowUncheck);
//     // only needed if elem can be pre-checked
//     elem.previous = elem.checked;
//   });

// function allowUncheck(e) {
//   if (this.previous) {
//     this.checked = false;
//   }
//   // need to update previous on all elements of this group
//   // (either that or store the id of the checked element)
//   document
//     .querySelectorAll(`input[type=radio][name=${this.name}]`)
//     .forEach((elem) => {
//       elem.previous = elem.checked;
//     });
// }

// function gf1_updateSelected() {
//   console.log('updateSelected')
// }

// if (!window.goldfinch) {
//   window.goldfinch = {};
// }

// window.goldfinch.iconSelect = function(v) {
//   gf1_updateSelected(v)
// }

// window.goldfinch.iconSearch = function(v) {
//   console.log(v)
// }

// var iconSearchFields = document.querySelectorAll("[data-icon-search-field]");

// document.addEventListener('DOMContentLoaded', () => {

//   iconSearchFields.forEach((el, k) => {
//     console.log(el,k)
//   })
// })

// // input.addEventListener("keypress", function(event) {
// //   console.log(event)
// // });
