

        const subcategories = {
            news: ['การเมือง', 'เศรษฐกิจ', 'สังคม', 'ต่างประเทศ'],
            sports: ['ฟุตบอล', 'บาสเกตบอล', 'วอลเลย์บอล', 'เทนนิส'],
            games: ['เกมคอมพิวเตอร์', 'เกมคอนโซล', 'เกมมือถือ', 'บอร์ดเกม'],
            music: ['ป๊อป', 'ร็อค', 'แจ๊ส', 'คลาสสิก'],
            lifestyle: ['แฟชั่น', 'อาหาร', 'ท่องเที่ยว', 'สุขภาพ', 'ความงาม']
        };

        function updateSubcategories() {
            const categorySelect = document.getElementById('postCategory');
            const subcategorySelect = document.getElementById('postSubcategory');
            const subcategoryContainer = document.getElementById('subcategoryContainer');
            const selectedCategory = categorySelect.value;

            // ล้างตัวเลือกเก่า
            subcategorySelect.innerHTML = '<option value="">เลือกประเภทย่อย</option>';

            if (selectedCategory && subcategories[selectedCategory]) {
                // เพิ่มตัวเลือกใหม่ตามหมวดหมู่ที่เลือก
                subcategories[selectedCategory].forEach(subcat => {
                    const option = document.createElement('option');
                    option.value = subcat;
                    option.textContent = subcat;
                    subcategorySelect.appendChild(option);
                });
                // แสดงฟิลด์ประเภทย่อย
                subcategoryContainer.style.display = 'block';
            } else {
                // ซ่อนฟิลด์ประเภทย่อยถ้าไม่ได้เลือกหมวดหมู่หรือไม่มีประเภทย่อย
                subcategoryContainer.style.display = 'none';
            }
        }

        function submitPost() {
            var title = document.getElementById('postTitle').value;
            var content = document.getElementById('postContent').value;
            var category = document.getElementById('postCategory').value;
            var subcategory = document.getElementById('postSubcategory').value;
            var imageFile = document.getElementById('postImage').files[0];

            if (title && content && category) {
                if (imageFile) {
                    resizeImage(imageFile, 300, 200, function(resizedImageUrl) {
                        savePostToDatabase(title, content, category, subcategory, resizedImageUrl);
                    });
                } else {
                    savePostToDatabase(title, content, category, subcategory);
                }
            } else {
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            }
        }

        function resizeImage(file, maxWidth, maxHeight, callback) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');
                    var width = img.width;
                    var height = img.height;

                    if (width > height) {
                        if (width > maxWidth) {
                            height *= maxWidth / width;
                            width = maxWidth;
                        }
                    } else {
                        if (height > maxHeight) {
                            width *= maxHeight / height;
                            height = maxHeight;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    callback(canvas.toDataURL(file.type));
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function savePostToDatabase(title, content, category, subcategory, imageUrl = null) {
            var formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            formData.append('category', category);
            formData.append('subcategory', subcategory);
            if (imageUrl) {
                formData.append('image', imageUrl);
            }

            fetch('save_post.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    createAndAddPost(title, content, category, subcategory, imageUrl, data.post_id, true);
                    alert('สร้างกระทู้สำเร็จ!');
                    
                    // ปิด Modal
                    var modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
                    modal.hide();

                    // รีเซ็ตฟอร์ม
                    document.getElementById('createPostForm').reset();
                    document.getElementById('subcategoryContainer').style.display = 'none';
                } else {
                    alert('เกิดข้อผิดพลาดในการสร้างกระทู้: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการสร้างกระทู้');
            });
        }

        function createAndAddPost(title, content, category, subcategory, imageUrl, postId, isOwnPost) {
            // สร้างกระทู้ใหม่
            var newPost = createPostElement(title, content, category, subcategory, imageUrl, postId, isOwnPost);
            
            // เพิ่มกระทู้ใหม่ในฟีดกระทู้
            var feedPosts = document.getElementById('feedPosts');
            if (feedPosts) {
                feedPosts.insertBefore(newPost, feedPosts.firstChild);
            }
            
            // เพิ่มกระทู้ใหม่ในหมวดหมู่ที่เกี่ยวข้อง
            var categoryPosts = document.getElementById(category + 'Posts');
            if (categoryPosts) {
                categoryPosts.insertBefore(newPost.cloneNode(true), categoryPosts.firstChild);
            }

            // แจ้งเตือนว่าสร้างกระทู้สำเร็จ
            alert('สร้างกระทู้สำเร็จ!\nหัวข้อ: ' + title + '\nหมวดหมู่: ' + category + (subcategory ? '\nประเภทย่อย: ' + subcategory : ''));

            // ปิด Modal
            var modal = bootstrap.Modal.getInstance(document.getElementById('createPostModal'));
            modal.hide();

            // รีเซ็ตฟอร์ม
            document.getElementById('createPostForm').reset();
            document.getElementById('subcategoryContainer').style.display = 'none';
        }

        function createPostElement(title, content, category, subcategory, imageUrl, postId, isOwnPost, author, createdAt) {
            var postElement = document.createElement('div');
            postElement.className = 'col-md-4 mb-4';
            postElement.innerHTML = `
                <div class="card">
                    ${imageUrl ? `<div class="card-img-container"><img src="${imageUrl}" class="card-img-top" alt="${title}"></div>` : ''}
                    <div class="card-body">
                        <h5 class="card-title">${title}</h5>
                        <p class="card-text">${content.substring(0, 100)}...</p>
                        <p class="card-text">
                            <small class="text-muted">
                                หมวดหมู่: ${category}${subcategory ? ', ' + subcategory : ''}
                            </small>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                                โดย: ${author} | ${new Date(createdAt).toLocaleString('th-TH')}
                            </small>
                        </p>
                        <a href="#" class="btn btn-primary">อ่านเพิ่มเติม</a>
                        <div class="post-actions">
                            <button class="btn btn-link like-btn" onclick="toggleLike(${postId})">
                                <i class="bi bi-heart"></i> <span class="like-count">0</span>
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-link" type="button" id="dropdownMenuButton${postId}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${postId}">
                                    ${isOwnPost ? `
                                        <li><a class="dropdown-item" href="#" onclick="editPost(${postId})">แก้ไข</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="deletePost(${postId})">ลบ</a></li>
                                    ` : ''}
                                    <li><a class="dropdown-item" href="#" onclick="reportPost(${postId})">รายงาน</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            return postElement;
        }

        function toggleLike(postId) {
            // ในที่นี้เราจะจำลองการเพิ่ม/ลดจำนวนไลค์
            var likeBtn = event.currentTarget;
            var likeCount = likeBtn.querySelector('.like-count');
            var currentLikes = parseInt(likeCount.textContent);
            
            if (likeBtn.classList.contains('liked')) {
                likeCount.textContent = currentLikes - 1;
                likeBtn.classList.remove('liked');
            } else {
                likeCount.textContent = currentLikes + 1;
                likeBtn.classList.add('liked');
            }
        }

        function editPost(postId) {
            // ฟังก์ชันสำหรับแก้ไขกระทู้
            alert('แก้ไขกระทู้ ID: ' + postId);
        }

        function deletePost(postId) {
            // ฟังก์ชันสำหรับลบกระทู้
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบกระทู้นี้?')) {
                alert('ลบกระทู้ ID: ' + postId);
                // ในที่นี้คุณจะต้องเพิ่มโค้ดเพื่อลบกระทู้จริง ๆ
            }
        }

        function reportPost(postId) {
            // ฟังก์ชันสำหรับรายงานกระทู้
            alert('รายงานกระทู้ ID: ' + postId);
        }


    document.querySelectorAll('.menu-item').forEach(item => {
      item.addEventListener('click', function() {
        const action = this.getAttribute('data-tooltip');
        switch(action) {
          case 'หน้าหลัก':
            window.location.href = 'index.php';
            break;
          case 'สร้างกระทู้':
            // เพิ่มโค้ดสำหรับการสร้างกระทู้
            console.log('สร้างกระทู้');
            break;
          case 'โปรไฟล์':
            // เพิ่มโค้ดสำหรับการไปยังหน้าโปรไฟล์
            console.log('ไปยังหน้าโปรไฟล์');
            break;
        }
      });
      
    });
