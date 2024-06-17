import { EventManager } from '../EventManager';

export class Comments extends EventManager {
  eventMappings() {
    return [
      { selector: 'form.anspress-comments-form', eventType: 'submit', handler: this.submitForm, cancel: true },
    ];
  }
  init() {
    console.log(this.data)
    // Validate post ID.
    if (!this.data?.postId) {
      console.error('Post ID not found.');
      return;
    }

    this.form = this.createForm();

    this.commentsCountNode = this.el('.anspress-comments-count');
    this.replyButton = this.el('.anspress-comments-add-comment-button');

    super.init();

    if (!this.data?.canComment) {
      this.elements['comments-toggle-form'].style.display = 'none';
    }
  }

  updateElements() {
    return {
      'comments-total-count': 'totalComments',
      'comments-showing-count': () => {
        const showing = this.data.showing + this.data.offset

        return showing > this.data.totalComments ? this.data.totalComments : showing;
      }
    };
  }

  createForm() {
    const form = document.createElement('form');
    form.setAttribute('class', 'anspress-comments-form');
    form.innerHTML = `
      <textarea name="comment" placeholder="Write your comment..."></textarea>
      <div class="anspress-comments-form-buttons">
        <button data-anspressel @click.prevent="toggleCommentForm" class="anspress-comments-form-cancel" type="button">Cancel</button>
        <button class="anspress-comments-form-submit" type="submit">Submit</button>
      </div>
    `;
    form.style.display = 'none';
    this.container.appendChild(form);
    return form;
  }

  toggleCommentForm() {
    if (this.form.style.display === 'none') {
      this.replyButton.style.display = 'none';
      this.form.style.display = 'block';
    } else {
      this.replyButton.style.display = 'inline-block';
      this.form.style.display = 'none';
    }
  }

  async submitForm(e, form) {
    e.preventDefault(); // Prevent default form submission behavior
    const comment = form.querySelector('textarea').value;
    try {
      const response = await this.fetch({
        path: `/anspress/v1/post/${this.data.postId}/comments`,
        method: 'POST',
        data: { comment }
      });

      if (response) {
        this.form.style.display = 'none';
        this.form.reset();

        // Create a temporary container to extract the newly added HTML element
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = response.html;
        const newComment = tempContainer.firstElementChild;

        // Insert the new comment into the DOM
        const commentsContainer = this.el('.anspress-comments-items');
        commentsContainer.insertAdjacentElement('afterbegin', newComment);

        // Scroll to the new comment
        newComment.scrollIntoView({ behavior: 'smooth', block: 'start' });

        // Apply fade-in animation using CSS class
        newComment.classList.add('fade-in');

        this.replyButton.style.display = 'inline-block';
      } else {
        alert('Failed to submit comment');
      }
    } catch (error) {
      console.error('An error occurred:', error);
    }
  }

  async loadMoreComments() {
    console.log(this.data)
    try {
      const response = await this.fetch({
        path: `/anspress/v1/post/${this.data.postId}/comments?offset=${(this.data.offset + this.data.showing)}`,
        method: 'GET'
      });

      if (response.html) {
        this.elements['comments-items'].insertAdjacentHTML('beforeend', response.html);
      } else {
        this.elements['comments-load-more'].style.display = 'none';
      }

      if (this.data.hasMore) {
        this.elements['comments-load-more'].style.display = 'inline-block';
      } else {
        this.elements['comments-load-more'].style.display = 'none';
      }
    } catch (error) {
      console.error('An error occurred while loading more comments:', error);
    }
  }

  async deleteComment(e, element) {
    const commentElement = element.closest('.anspress-comments-item');
    const commentId = element.dataset.commentId;

    try {
      const response = await this.fetch({
        path: `/anspress/v1/post/${this.data.postId}/comments/${commentId}`,
        method: 'DELETE'
      });

      if (response) {
        commentElement.remove();
      } else {
        alert('Failed to delete comment');
      }
    } catch (error) {
      console.error('An error occurred while deleting the comment:', error);
    }
  }

  updateLoadMoreButton() {
    if (this.itemsShowing >= this.totalItems) {
      this.elements['comments-load-more'].style.display = 'none';
    } else {
      this.elements['comments-load-more'].style.display = 'block';
    }
  }

  updateCommentsCount() {
    if (this.elements['comments-total-count']) {
      this.elements['comments-total-count'].textContent = this.data.totalComments;
    }

    if (this.elements['comments-showing-count']) {
      this.elements['comments-showing-count'].textContent = this.data.showing;
    }
  }
}

