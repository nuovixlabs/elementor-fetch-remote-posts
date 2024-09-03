// efrp-script.js
(function ($) {
  var EFRPLoader = function (element, options) {
    this.$element = $(element);
    this.settings = options;
    this.init();
  };

  EFRPLoader.prototype = {
    init: function () {
      this.showLoading();
      this.fetchPosts();
    },

    showLoading: function () {
      this.$element.html('<div class="efrp-loading">Loading posts...</div>');
    },

    fetchPosts: function () {
      var self = this;
      $.ajax({
        url: efrp_ajax.ajax_url,
        type: "POST",
        data: {
          action: "efrp_fetch_posts",
          nonce: efrp_ajax.nonce,
          settings: JSON.stringify(this.settings), // This now includes cache_time
        },
        success: function (response) {
          if (response.success) {
            self.renderPosts(response.data);
          } else {
            self.showError(response.data);
          }
        },
        error: function () {
          self.showError("An error occurred while fetching posts.");
        },
      });
    },

    renderPosts: function (posts) {
      if (posts.length === 0) {
        this.$element.html('<div class="efrp-no-posts">No posts found.</div>');
        return;
      }

      var html = $("<div>").addClass("efrp-posts-list");
      if (this.settings.layout === "grid") {
        html.addClass("efrp-grid");
      }

      $.each(
        posts,
        function (index, post) {
          html.append(this.renderPost(post));
        }.bind(this)
      );

      this.$element.html(html);
    },

    renderPost: function (post) {
      var title = this.trimWords(post.title.rendered, this.settings.title_length);
      var excerpt = this.trimWords(
        post.excerpt.rendered || post.content.rendered,
        this.settings.excerpt_length
      );
      var imageUrl =
        post._embedded &&
        post._embedded["wp:featuredmedia"] &&
        post._embedded["wp:featuredmedia"][0]
          ? post._embedded["wp:featuredmedia"][0].source_url
          : "";

      var postHtml = $("<div>").addClass("efrp-post");

      if (imageUrl) {
        postHtml.append(
          $("<div>")
            .addClass("efrp-post-image")
            .append(
              $("<a>")
                .attr("href", post.link)
                .append($("<img>").attr("src", imageUrl).attr("alt", title))
            )
        );
      }

      postHtml.append(
        $("<div>")
          .addClass("efrp-post-content")
          .append(
            $("<h3>")
              .addClass("efrp-post-title")
              .append($("<a>").attr("href", post.link).text(title)),
            $("<div>").addClass("efrp-post-excerpt").html(excerpt)
          )
      );

      return postHtml;
    },

    trimWords: function (text, length) {
      return $("<div>").html(text).text().split(" ").slice(0, length).join(" ");
    },

    showError: function (message) {
      this.$element.html('<div class="efrp-error">' + message + "</div>");
    },
  };

  $.fn.efRPLoader = function (options) {
    return this.each(function () {
      new EFRPLoader(this, options);
    });
  };

  $(document).ready(function () {
    $(".efrp-container").each(function () {
      var $this = $(this);
    //   $this.efRPLoader(JSON.parse($this.data("settings")));
    $this.efRPLoader($this.data("settings"));
    });
  });
})(jQuery);
