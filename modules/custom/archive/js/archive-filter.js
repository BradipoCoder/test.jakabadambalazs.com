/**
 * Archive Filter JS
 */

(function ($, Drupal) {
  Drupal.behaviors.arch2 = {
    attach: function (context, settings) {
      // Using once() to apply the myCustomBehaviour effect when you want to do just run one function.
      // $('body', context).once('myCustomBehavior').addClass('well');
      var self = this;

      $('#wrapper-archive', context).once('wrapper-archive').each(function(){
        self.armCheckboxes(context, self);
        self.armRadio(context, self);
        self.armMobileToggle(context, self);
        self.armReset(context, self);
        self.armBundleSelect(context, self);
        self.armSearch(context, self);
        self.armRemoveSearch(context, self);
        self.armCopyUrl(context, self);

        // Armo la paginazione iniziale
        var nids = self.getArchiveNids(context, self);
        self.createPagination(context, self, nids, 1);

        // Controllo i dati passati negli argomenti
        var filters = self.checkDataGet(context, self);
        if (filters){
          $('#wrapper-archive').attr('data-action', 'reload');
          // Update markup filtri
          self.updateFilterLinks(context, self, filters);

          // Update markup filtri
          var data = self.createData(context, self);
        } else {
          // Check cookies
          var data = self.checkCookies(context, self);
        }

        // Refresh archive        
        self.refreshArchive(context, self, data);
      });
    },
    armCheckboxes: function(context, self){
      $('.filter-link-checkbox', context).click(function(e){
        e.preventDefault();

        var filter_name = $(this).attr('data-filter-name');
        var checkboxes = $('#filter-name-' + filter_name);

        if (!$(this).hasClass('disabled')){
          var check = $(this);
          check.toggleClass('checked');
        }

        // Aggiorno i valori del checkbox
        self.updateCheckboxesValue(context, self, checkboxes);

        // Refresh archive
        self.refreshArchive(context, self);
      });
    },
    armRadio: function(context, self){
      $('.filter-link-radio', context).click(function(e){
        e.preventDefault();

        var filter_name = $(this).attr('data-filter-name');
        var radios = $('#filter-name-' + filter_name);

        var radio = $(this);
        var icon = $('i', radio);
        var value = false;

        if (!radio.hasClass('disabled')){
          if (radio.hasClass('checked')){
            // Tolgo il check
            radio.removeClass('checked');
          } else {
            // Tolgo il check a tutti, e lo metto solo a quello cliccato
            $('.filter-link-radio', radios).removeClass('checked');
            radio.addClass('checked');
            value = radio.attr('data-value');
          }
          // Aggiorno il valore del radio
          radios.attr('data-value', value);
          var data = self.createData(context, self);
          
          // Refresh archive
          self.refreshArchive(context, self);
        }
      });
    },
    /**
     * Recupera i filtri salvati negli argomenti
     */
    checkDataGet: function(context, self){
      var dataGet = $('#wrapper-archive').attr('data-get');
      if (dataGet){
        filters = JSON.parse(dataGet);

        // Resetto tutti i filtri se nell'URL c'è l'argomento reset
        if (filters.reset){
          filters = [];
        }

        return filters;
      }
      return false;
    },
    /**
     * Crea un array con tutti i dati relativi ai filtri (presi dal DOM)
     */
    createData: function(context, self){
      var active_filters = 0;
      var data = {
        'filters': {},
        'nids': $('#archive-nids').attr('data-value'),
      };
      $('.filter', context).each(function(){
        var filter_name = $(this).attr('data-filter-name');
        var value = $(this).attr('data-value');
        if (value !== 'false'){
          active_filters++;
        }
        data.filters[filter_name] = value;
      });

      var query = $('#archive-query').attr('data-value');
      data.filters['query'] = query;
      if (query !== 'false'){
        active_filters++;  
      }

      data.activeFilters = active_filters;
      self.saveDataInCookies(context, self, data);
      return data;
    },
    /**
     * Salvo i dati di filtro nei cookie
     */
    saveDataInCookies: function (context, self, data){
      // Rimuovo tutti i nid dalla variabile data prima di salvarlo nei cookies
      var c_data = _.omit(data, 'nids');
      Cookies.set('archive-data', c_data, { expires: 1 });
    },
    /**
     * Controllo i cookie e resituisco un oggetto data
     */
    checkCookies: function(context, self){
      var data = Cookies.get('archive-data');
      //console.debug(data, 'data check cookies');
      if (data !== undefined){
        data = JSON.parse(data);

        // Aggiorno il markup dei filtri
        self.updateFilterLinks(context, self, data.filters);

        // Devo ricostriuire l'oggetto data
        // e passarlo a update filter
        var data = self.createData(context, self);

        return data;
      }
      return false;
    },
    /**
     * Funzione per fare il refresh tutto l'archivio
     * allo scatenarsi di qualche evento
     * l'importante è che i valori dei filtri nel DOM siano aggiornati
     */
    refreshArchive: function(context, self, data = false){
      if (!data){
        // Ricreo la variabile data
        var data = self.createData(context, self);  
      }

      //console.debug(data.filters);

      // Aggiorno il title dell'header
      self.updateHeaderTitle(context, self, data);

      // Update dei tag per rimuovere il filtro
      self.updateMarkupTagRemove(context, self, data.filters);

      var wrapper = $('#wrapper-archive');

      // Se la pagina deve essere ricaricata mi fermo prima
      if (wrapper.attr('data-action') == 'do-reload'){
        self.reloadArchive(context, self);
        return;     
      }

      // Se la pagina deve essere ricaricata mi fermo prima
      if (wrapper.attr('data-action') == 'reload'){
        wrapper.attr('data-action', 'do-reload');    
      }

      // Update filtri e nodi
      self.updateFilter(context, self, data);

      // Update dell'url (per copia)
      self.setUrlArchive(context, self, data);
    },
    /**
     * Aggiorna la stringa del titolo dell'archivio
     * Aggiorna la label della chiave di ricerca
     */
    updateHeaderTitle: function(context, self, data){
      var title = $('#archive-title');
      var sub = $('#archive-sub');
      var headLabel = $('#query-head-label');

      // Query
      var query = $('#archive-query').attr('data-value');
      if (query == 'false'){
        headLabel.html("");  
      } else {
        headLabel.html('"' + query + '"');
      }

      // Filtered state
      if (data.activeFilters == 0){        
        $('.all', title).removeClass('hide');
        $('.filtered', title).addClass('hide');
        sub.removeClass('hide');
        
      } else {
        $('.all', title).addClass('hide');
        $('.filtered', title).removeClass('hide');
        sub.addClass('hide');
      }
    },
    /**
     * Update del markup dei link all'interno del filtro
     */
    updateFilterLinks: function(context, self, filters){
      // Aggiorno il markup dei filtri
      _.each(filters, function(value, key) {
        if (key == 'query'){
          $('#archive-query').attr('data-value', value);
          if (value == 'false'){
            $('#archive-search').val('');  
          } else {
            $('#archive-search').val(value);
          }
          
        } else {
          var filter = $('#filter-name-' + key);
          if (value !== 'false'){
            filter.attr('data-value', value);
            var values = value.split(',');
            _.each(values, function(v, key){
              $("a[data-value='" +  v + "']", filter).addClass('checked');
            });
          }  
        }
      });
    },
    /**
     * Update del markup dei tag per rimuovere i filtri
     */
    updateMarkupTagRemove(context, self, filters){
      var tagRemoveList = $('#tag-remove-list');
      var tags = '';

      // Remove dei filtri
      _.each(filters, function(value, key) {
        if (key == 'query' && value !== 'false'){
          // Query
          // tags = tags + '<a href="#" class="btn btn-info tag-remove" data-filter-id="archive-query">' + value + ' <i class="material-icons">close</i></a>';
        } else {
          // Filtri classici
          if (value !== 'false'){
            var filter = $('#filter-name-' + key);
            var values = value.split(',');
            _.each(values, function(v, k){
              var item = $("a[data-value='" +  v + "']", filter);
              var id = item.attr('id');
              var name = item.attr('data-name');
              tags = tags + '<li><a href="#" class="btn btn-primary tag-remove" data-filter-link-id="' + id + '" data-filter-id="filter-name-' + key + '">' + name + ' <i class="material-icons">close</i></a></li>';
            });
          }  
        }
        
      });
      tagRemoveList.html(tags);

      if (tags !== ''){
        $('.tag-remove').click(function(e){

          // Se è un tag ricerca, bisogna cambiare il comportamento
          e.preventDefault();
          var item = $(this);
          var flid = '#' + item.attr('data-filter-link-id');
          var filterid = '#' + item.attr('data-filter-id');
          $(flid).removeClass('checked');

          var checkboxes = $(filterid);
          self.updateCheckboxesValue(context, self, checkboxes);

          // @todo: è in più?
          self.refreshArchive(context, self);
        })
      }

      // Remove della ricerca
      var lens = $('#archive-search-lens');
      var remove = $('#archive-search-remove');
      if (filters.query !== 'false'){
        lens.addClass('hide');
        remove.removeClass('hide');
      } else {
        lens.removeClass('hide');
        remove.addClass('hide');
      }
    },
    /**
     * Aggiorna i filtri possibili in base ai risultati
     * Solo se viene passato un oggetto data
     */
    updateFilter: function(context, self, data){
      if (!data){
        return;
      }
      var aurl = '/archive/update-filter.json';
      var wrapper = $('#wrapper-archive-filters');
      var dom_nids = $('#filtered-nids');
      var tmp_nids = dom_nids.attr('data-value');

      // Aggiorno il numero dei filtri attivi
      if (data.activeFilters !== 0){
        $('#active-filters').html(data.activeFilters);
      } else {
        $('#active-filters').html('');
      }

      // Aggiungo classi attive a seconda dei filtri checkati
      // Utili per lavorare in css
      _.each(data.filters, function(value, key){
        if (key !== 'query'){
          var filter = $('#filter-name-' + key);
          if (value !== 'false'){
            filter.addClass('active');
            wrapper.addClass('on-' + key);
          } else {
            filter.removeClass('active');
            wrapper.removeClass('on-' + key);
          }  
        }
      });

      var data_json = JSON.stringify(data);      
      wrapper.addClass('loading');

      $.ajax({
        url: aurl,
        data: data_json,
        method: 'POST',
      }).done(function(result) {
        if (result['nids'].length !== 0){
          // Aggiorno i filtri e i nids filtrati
          dom_nids.attr('data-value', result['nids']);
          var filters = result['filters'];
          
          _.each(filters, function(filter, key) {
            // console.debug('active options for ' + key, filter);
            if (key !== 'query'){
              // Dentro filter ci sono i valori possibili
              // Recupero il filtro del dom e disattivo i link non presenti
              var dom_filter = $('#filter-name-' + key);
              
              var n_active = 0;
              $('.filter-link', dom_filter).each(function(){
                var value = $(this).attr('data-value');
                value = parseInt(value);
                if (_.indexOf(filter, value) == -1){
                  $(this).addClass('disabled');
                } else{
                  $(this).removeClass('disabled');
                  n_active++;
                }
              });

              // Se tutte le opzioni sono spente, nascondo il filtro
              if (n_active == 0){
                dom_filter.addClass('hide');
              } else {
                dom_filter.removeClass('hide');
              }  
            }
          });
        }

        wrapper.removeClass('loading');

        // Aggiorno i prodotti
        self.updateProducts(context, self, result['nids'], 1);

        // Aggiorno la ricerca
        self.updateSearchData(context, self);
        
      });
    },
    /**
     * Aggiorna i prodotti della pagina
     */
    updateProducts: function(context, self, nids, active_page){
      
      // Visualizzo il numero di prodotti disponibili
      var count = nids.length;
      var archiveCount = $('#archive-count');
      archiveCount.removeClass('hide');
      $('span', archiveCount).html(count);

      // Creo la pagination e, se necessario, filtro i risultati
      var page_nids = self.createPagination(context, self, nids, active_page);

      // Empty message
      var empty = $('#archive-no-results');
      if (count){
        empty.addClass('hide');
      } else {
        empty.removeClass('hide');
      }
        
      // I nid da caricare in pagina
      var data = {
        'nids': page_nids,
      };
      var data_json = JSON.stringify(data);

      var langCode = drupalSettings.path.currentLanguage;

      var aurl = '/' + langCode + '/archive/getdata';
      var destination = $('#ajax-archive-destination');
      var wrapper = $('#wrapper-archive-results');
      wrapper.addClass('loading-line');

      $.ajax({
        url: aurl,
        data: data_json,
        method: 'POST',
      }).done(function(result) {
        var response = jQuery('<html />').html(result);
        var content = jQuery('#wrapper-results .node-product', response);
        destination.html(content);
        Drupal.attachBehaviors(context, drupalSettings);
        wrapper.removeClass('loading-line');

        self.setFilterHeight(context, self);
      });
    },
    /**
     * Aggiorna i valori del checkbox dopo che sono stati modificati
     * passare un filtro completo nella variabile checkboxes
     */
    updateCheckboxesValue: function(context, self, checkboxes){
      var checked = [];
      $('.checked', checkboxes).each(function(){
        checked.push($(this).attr('data-value'));
      });
      if (_.isEmpty(checked)){
        checkboxes.attr('data-value', 'false');
      } else {
        checkboxes.attr('data-value', checked);
      }  
    },
    /**
     * Questa funziona crea la paginazione e ritorna, se necessario i nid filtrati
     */
    createPagination: function(context, self, items, active_page){
      var wrapper = $('#wrapper-archive-results');
      var pagination = $('#archive-pagination');
      wrapper.removeClass('with-pagination');
      pagination.removeClass('p-active');
      var n = items.length;
      var per_page = parseInt(pagination.attr('data-per-page'));
      
      // Attivo la pagination
      if (n > per_page){

        // Visualizzo la pagination
        pagination.addClass('p-active');
        wrapper.addClass('with-pagination');

        // documentation: https://github.com/flaviusmatis/simplePagination.js
        pagination.pagination({
          items: n,
          itemsOnPage: per_page,
          displayedPages: 3,
          edges: 1,
          hrefTextPrefix: '#',
          ellipsePageSet: false,
          cssStyle: null,
          currentPage: active_page,
          prevText: '<i class="material-icons">keyboard_arrow_left</i>',
          nextText: '<i class="material-icons">keyboard_arrow_right</i>',
          //useStartEdge : false,
          //useEndEdge : false,
          onPageClick: function(pageNumber, event) {
            paginationUsefullClass();
            pagination.attr('data-active-page', pageNumber);

            var nids = self.getFilteredNids(context, self);
            self.updateProducts(context, self, nids, pageNumber);

            scrollTo('#archive-title');
          },
          onInit: function() {
            paginationUsefullClass();  
          }
        });

        function paginationUsefullClass(){
          var pagination = $('#archive-pagination');
          $('.page-link, span.current, span.ellipse', pagination).not('.prev, .next').parent().addClass('li-item').last().addClass('li-item-last');
          $('.prev', pagination).parent().addClass('li-arrow li-arrow-prev');
          $('.next', pagination).parent().addClass('li-arrow li-arrow-next');  
        }
        
        // Filtro gli items in base alla pagina
        if (active_page == 1){
          items = _.first(items, per_page);  
        } else {
          var start = ((active_page - 1) * per_page);
          var end = start + per_page;
          items = items.slice(start, end);
        }
      }
      return items;
    },
    /**
     * Ritorna i nid da mostrare all'utente (tutti)
     */
    getArchiveNids: function(context, self){
      var nids = $('#archive-nids').attr('data-value');
      // C'è un problema.. i nodi ci devono essere
      if (nids == undefined){
        console.debug('Missing nids');
        return false;
      }
      nids = nids.split(',');
      return nids;
    },
    /**
     * Ritorna i nid filtrati
     */
    getFilteredNids: function(context, self){
      var nids = $('#filtered-nids').attr('data-value');
      nids = nids.split(',');
      return nids;
    },
    /**
     * Armo la funzionalità che apre e chiude i filtri nel mobile
     */
    armMobileToggle: function(context, self){
      var toggle = $('#archive-mobile-toggle');
      var filters = $('#wrapper-archive-filters');
      toggle.click(function(e){
        e.preventDefault();
        filters.toggleClass('mobile-open');
      });

      var close = $('#archive-mobile-close');
      close.click(function(e){
        e.preventDefault();
        filters.removeClass('mobile-open');
      });
    },
    /**
     * Armo la funzionalità reset
     */
    armReset: function(context, self){
      var reset = $('#archive-reset, #archive-reset-empty');

      reset.click(function(e){
        e.preventDefault();
        var links = $('.filter-link');
        var filters = $('.filter');
        links.removeClass('disabled').removeClass('checked');
        filters.attr('data-value', 'false');
        $('#wrapper-archive-filters').removeClass('mobile-open');

        $('#archive-query').attr('data-value', 'false');
        $('#archive-search').val('');

        // Animo l'icona del reset
        var icon = $('i', reset);
        icon.addClass('fa-spin text-primary');
        setTimeout(function(){
          icon.removeClass('fa-spin text-primary');
        }, 2000);

        self.refreshArchive(context, self);
      });
    },
    reloadArchive: function(context, self){
      window.location.href = drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'products/archive';
    },
    armBundleSelect: function(context, self){
      var toggle = $('#bundle-select');
      var list = $('#bundle-select-list');
      toggle.click(function(e){
        e.preventDefault();
        list.toggleClass('open');
      });
    },
    /**
     * Arma la ricerca
     * La lista per l'autocomplite è generata dai filtri attivi
     */
    armSearch: function(context, self){
      var input = $('#archive-search');

      input.autocomplete({
        source: function(request, response){
          response(self.getSearchData(context, self, request));  
        },
        select: function( event, ui ){
          if (ui.item.filter !== false){
            self.checkFilter(context, self, ui.item.filter, ui.item.tid);
            self.refreshArchive(context, self);
            $(this).val('');
            event.preventDefault();
          } else {
            // Setto il valore della query
            $('#archive-query').attr('data-value', ui.item.value);
            
            // Faccio il refresh dell'archivio
            self.refreshArchive(context, self);
            // $(this).val('');
            // event.preventDefault();
          }
        },
        //autoFocus: true,
      }).autocomplete('instance')._renderItem = function(ul, item){
        if (item.filter){
          // Filters
          return $('<li>')
            .attr('data-value', item.tid )
            .append('<span class="h5 text-primary">' + item.title  +'</span> <span class="small search-label">' + item.label + '</span>')
            .appendTo(ul);  
        } else {
          // Search
          return $('<li>')
            .attr('data-value', item.tid )
            .append('<span class="small">' + item.title  +'</span> <span class="small search-label">' + item.label + '</span>')
            .appendTo(ul);
        }
      };

      // Click sulla lente
      var lens = $('#archive-search-lens');
      lens.click(function(e){
        e.preventDefault();
        var value = input.val();
        if (value == ''){
          value = 'false';
        }
        $('#archive-query').attr('data-value', value);

        // Faccio il refresh dell'archivio
        self.refreshArchive(context, self);
      });

      // Return key
      input.keydown(function(event){
        if(event.keyCode == 13) {
          var value = input.val();
          if (value == ''){
            value = 'false';
          }
          $('#archive-query').attr('data-value', value);

          // Faccio il refresh dell'archivio
          self.refreshArchive(context, self);
          input.blur();
        }
      });

      // Lens active class
      input.keyup(function(event){
        if (input.val().length !== 0){
          lens.addClass('active');
        } else {
          lens.removeClass('active');
        }

        // Cancello tutte le lettere
        if(event.keyCode == 8) {
          var value = input.val();
          if (value.length == 0){
            $('#archive-query').attr('data-value', 'false');
            // Faccio il refresh dell'archivio
            self.refreshArchive(context, self); 
          }
        }
      });
      // Active class on focus
      input.on('focus', function(){
        lens.addClass('active');
      })
      input.on('focusout', function(){
        lens.removeClass('active');
      })

      // Se nel data get c'è il parametro focus
      var dataGet = $('#wrapper-archive').attr('data-get');
      if (dataGet){
        dataGet = JSON.parse(dataGet);
        if (dataGet.focus){
          input.focus();
        }
      }
    },
    /**
     * Cancella la ricerca e aggiorna l'archivio
     */
    armRemoveSearch: function(context, self){
      $('#archive-search-remove').click(function(e){
        e.preventDefault();
        var input = $('#archive-search');
        input.val('');
        //input.focus();
        $('#archive-query').attr('data-value', 'false');
        // Faccio il refresh dell'archivio
        self.refreshArchive(context, self); 
      })
    },
    /**
     * [getSearchData description]
     * @param  {[type]} context [description]
     * @param  {[type]} self    [description]
     * @param  {[type]} request [description]
     * @return {[type]}         [description]
     */
    getSearchData: function(context, self, request){
      var list = [];

      // Add user search
      // if (request !== undefined){
      //   list.push({
      //     value: request.term,
      //     label: '"' + request.term + '"',
      //     filter: false,
      //     title: 'Cerca',
      //     tid: false,
      //   });
      // }

      // Possible filter
      $('.filter-link').not('.disabled').not('.checked').each(function(){
        var link = $(this);
        var value = link.attr('data-value');
        var name = link.attr('data-name');
        var filter = link.attr('data-filter-name');
        var title = link.parents('.filter').attr('data-filter-title');

        list.push({
          value: name,
          label: name,
          filter: filter,
          title: title,
          tid: value,
        });
      });

      // Split words
      if (request !== undefined){
        var term = request.term.trim();
        var words = term.split(' ');
        if (words.length > 1){
          var pro = [];
          _.each(words, function(word, key) {
            if (word.length > 2){
              // Supertricks "carrell" instead of "carelli"
              if (word.length > 3){
                word = word.substring(0, word.length - 1);
              }
              var l = $.ui.autocomplete.filter(list, word);
              pro = _.union(pro, l);  
            }
          });
          list = pro;
        } else {
          // Supertricks "carrell" instead of "carelli"
          if (term.length > 3){
            term = term.substring(0, term.length - 1);  
          }
          list = $.ui.autocomplete.filter(list, term);    
        }   
      }

      return list;
    },
    updateSearchData: function(context, self){
      var list = self.getSearchData(context, self);
      //$("#archive-search").autocomplete( "option", "source", list);
    },
    /**
     * Check a Filter
     */
    checkFilter: function(context, self, name, tid){
      var checkboxes = $('#filter-name-' + name);
      var link = $('#filter-link-' + name + '-' + tid).addClass('checked');
      self.updateCheckboxesValue(context, self, checkboxes);
    },
    armCopyUrl: function(context, self){
      var clipboard = new ClipboardJS('#copy-url');
      var item = $('#copy-url');
      item.click(function(){
        item.addClass('text-green');
        setTimeout(function() {
          item.removeClass('text-green');
        }, 1000);
      }); 
    },
    setUrlArchive: function(context, self, data){
      var url = drupalSettings.path.baseUrl + drupalSettings.path.pathPrefix + 'products/archive';
      $('#copy-url').attr('data-clipboard-text', url);
      if (data.filters){
        var filters = data.filters;
        var n = 0;
        url += '?';
        _.each(filters, function(value, key) {
          if (value !== 'false'){
            if(n == 0){
              url += key + '=' + value;   
            } else {
              url += '&' + key + '=' + value;
            }
            n++;
          }
        });  
        $('#copy-url').attr('data-clipboard-text', url);
      }   
    },
    setFilterHeight: function (context, self){
      setTimeout(function(){
        var right = $('#wrapper-archive-results');
        var right_height = right.outerHeight();
        var left = $('#wrapper-archive-filters');
        var left_height = left.outerHeight();

        if (left_height <= right_height){
          left.css('min-height', right_height);
        } else {
          left.css('min-height', 'auto');
        }

        //console.debug('------------');
        //console.debug(left_height, 'left');
        //console.debug(right_height, 'right');
      }, 500);
    }
  };
})(jQuery, Drupal);