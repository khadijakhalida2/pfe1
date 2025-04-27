document.addEventListener('DOMContentLoaded', function() {
    const userTypeBtns = document.querySelectorAll('.user-type-btn');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const passwordMatch = document.getElementById('passwordMatch');
    const passwordStrengthBar = document.getElementById('passwordStrengthBar');
    const extraFields = document.getElementById('extraFields');
    const parentFields = document.getElementById('parentFields');
    const submitBtn = document.getElementById('submitBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const submitText = document.getElementById('submitText');
    let currentUserType = 'parent';

    // حقول الأخصائي
    const specialistFields = `
      <div id="specialistFields">
        <div class="mb-3">
          <label for="specialty" class="form-label">التخصص</label>
          <select class="form-select" id="specialty" required>
            <option value="" selected disabled>اختر التخصص</option>
            <option value="psychologist">أخصائي نفسي</option>
            <option value="speech_therapist">أخصائي أرطفوني</option>
            <option value="occupational_therapist">أخصائي علاج وظيفي</option>
            <option value="behavioral_therapist">أخصائي علاج سلوكي</option>
          </select>
          <div class="error-message" id="specialtyError">التخصص مطلوب</div>
        </div>
        <div class="mb-3">
          <label for="license" class="form-label">رقم الرخصة المهنية</label>
          <input type="text" class="form-control" id="license" required>
          <div class="error-message" id="licenseError">رقم الرخصة المهنية مطلوب</div>
        </div>
      </div>
    `;

    // اختيار نوع المستخدم
    userTypeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            userTypeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentUserType = this.dataset.userType;
            
            // تحديث الحقول الإضافية
            if(currentUserType === 'parent') {
                extraFields.innerHTML = parentFields.outerHTML;
            } else {
                extraFields.innerHTML = specialistFields;
            }
        });
    });

    // التحقق من قوة كلمة المرور
    passwordInput.addEventListener('input', function(e) {
        const password = e.target.value;
        let strength = 0;
        
        if(password.length >= 8) strength += 1;
        if(password.length >= 12) strength += 1;
        if(/[A-Z]/.test(password)) strength += 1;
        if(/[0-9]/.test(password)) strength += 1;
        if(/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        const width = (strength / 5) * 100;
        passwordStrengthBar.style.width = `${width}%`;
        
        if(strength <= 2) {
            passwordStrengthBar.style.backgroundColor = '#dc3545';
        } else if(strength <= 4) {
            passwordStrengthBar.style.backgroundColor = '#fd7e14';
        } else {
            passwordStrengthBar.style.backgroundColor = '#198754';
        }
    });

    // التحقق من تطابق كلمة المرور
    confirmPasswordInput.addEventListener('input', function() {
        if(passwordInput.value && confirmPasswordInput.value) {
            if(passwordInput.value === confirmPasswordInput.value) {
                passwordMatch.textContent = "كلمات المرور متطابقة";
                passwordMatch.classList.remove('invalid');
                passwordMatch.classList.add('valid');
                passwordMatch.style.display = 'block';
            } else {
                passwordMatch.textContent = "كلمات المرور غير متطابقة";
                passwordMatch.classList.remove('valid');
                passwordMatch.classList.add('invalid');
                passwordMatch.style.display = 'block';
            }
        } else {
            passwordMatch.style.display = 'none';
        }
    });

    // معالجة تسجيل الحساب
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // إخفاء جميع رسائل الخطأ
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });

        // جمع البيانات من النموذج
        const formData = {
            user_type: currentUserType,
            fullName: document.getElementById('fullName').value.trim(),
            email: document.getElementById('email').value.trim(),
            password: document.getElementById('password').value,
            confirmPassword: document.getElementById('confirmPassword').value
        };
        
        // إضافة حقول إضافية حسب نوع المستخدم
        if(currentUserType === 'parent') {
            formData.childName = document.getElementById('childName').value.trim();
            formData.childAge = document.getElementById('childAge').value;
            formData.autismLevel = document.getElementById('autismLevel').value;
        } else {
            formData.specialty = document.getElementById('specialty').value;
            formData.license = document.getElementById('license').value.trim();
        }
        
        // التحقق من صحة البيانات قبل الإرسال
        if(!validateFormData(formData)) {
            return;
        }
        
        // إرسال البيانات إلى السيرفر
        sendRegistrationData(formData);
    });

    // دالة التحقق من صحة البيانات
    function validateFormData(data) {
        let isValid = true;
        
        // التحقق من الحقول المطلوبة
        if(!data.fullName) {
            document.getElementById('fullNameError').style.display = 'block';
            isValid = false;
        }
        
        if(!data.email) {
            document.getElementById('emailError').style.display = 'block';
            isValid = false;
        } else if(!validateEmail(data.email)) {
            document.getElementById('emailError').style.display = 'block';
            isValid = false;
        }
        
        if(!data.password) {
            document.getElementById('passwordError').style.display = 'block';
            isValid = false;
        } else if(data.password.length < 8) {
            document.getElementById('passwordError').textContent = 'كلمة المرور يجب أن تحتوي على 8 أحرف على الأقل';
            document.getElementById('passwordError').style.display = 'block';
            isValid = false;
        }
        
        if(data.password !== data.confirmPassword) {
            passwordMatch.textContent = "كلمات المرور غير متطابقة";
            passwordMatch.classList.remove('valid');
            passwordMatch.classList.add('invalid');
            passwordMatch.style.display = 'block';
            isValid = false;
        }
        
        if(!document.getElementById('agreeTerms').checked) {
            document.getElementById('agreeTermsError').style.display = 'block';
            isValid = false;
        }
        
        // التحقق من حقول ولي الأمر
        if(data.user_type === 'parent') {
            if(!data.childName) {
                document.getElementById('childNameError').style.display = 'block';
                isValid = false;
            }
            
            if(!data.childAge || data.childAge < 1 || data.childAge > 18) {
                document.getElementById('childAgeError').style.display = 'block';
                isValid = false;
            }
            
            if(!data.autismLevel) {
                document.getElementById('autismLevelError').style.display = 'block';
                isValid = false;
            }
        } else {
            // التحقق من حقول الأخصائي
            if(!data.specialty) {
                document.getElementById('specialtyError').style.display = 'block';
                isValid = false;
            }
            
            if(!data.license) {
                document.getElementById('licenseError').style.display = 'block';
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // دالة التحقق من صحة البريد الإلكتروني
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    // دالة إرسال البيانات إلى السيرفر
    function sendRegistrationData(data) {
        // عرض مؤشر التحميل
        loadingSpinner.style.display = 'inline-block';
        submitText.textContent = 'جاري إنشاء الحساب...';
        submitBtn.disabled = true;
        
        fetch('api/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if(!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if(data.success) {
                alert(data.message);
                window.location.href = data.redirect;
            } else {
                showErrorMessages(data.errors);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء الاتصال بالسيرفر: ' + error.message);
        })
        .finally(() => {
            // إخفاء مؤشر التحميل
            loadingSpinner.style.display = 'none';
            submitText.textContent = 'إنشاء الحساب';
            submitBtn.disabled = false;
        });
    }
    
    // دالة لعرض رسائل الخطأ
    function showErrorMessages(errors) {
        errors.forEach(error => {
            const field = Object.keys(error)[0];
            const message = error[field];
            const errorElement = document.getElementById(`${field}Error`);
            
            if(errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            } else {
                alert(message);
            }
        });
    }
});