$(function() {

    let Article = Backbone.Model.extend({
        "nid": "",
        "type": "",
        "title": "",
        "body": "",
        "field_date_programmed": "",
        "field_total_distance": "",
        "field_training_type": ""
    });

    var Articles = Backbone.Collection.extend({
        model: Article,
        url: 'https://tests.jakabadambalazs.com/rest/trainings-listing?_format=hal_json'
    });

    var ArticleView = Backbone.View.extend({
        tagName: 'li',

        template: _.template($('#article-view').html()),

        render: function() {
            this.$el.html(this.template(this.model.toJSON()));

            return this;
        }
    });

    var ArticlesList = Backbone.View.extend({
        el: $('#main-container'),

        template: _.template($('#articles-list').html()),

        initialize: function() {
            self = this;
            this.collection.fetch({
                success: function() {
                    self.render();
                }
            });
        },

        render: function() {
            this.el.innerHTML = this.template();
            var ul = this.$el.find('ul');
            this.collection.forEach( function(model) {
                ul.append((new ArticleView({ model: model })).render().el);
            }, this);

            return this;
        }
    });

    let App = new ArticlesList({ collection: new Articles });

});