// laravel port 8000
const apiUrl = 'http://localhost:8000';

function parseToken(token) {
    try {
        const tokenData = JSON.parse(atob(token.split('.')[1]));
        return tokenData;
    } catch (error) {
        console.error('Error parsing token:', error);
        return null;
    }
}

// DOM on load
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('message').textContent = '';
    document.querySelector('.createUser').style.display = 'none';

    const token = localStorage.getItem('memory');
    console.log(token)
    if (token != "undefined") {
        const tokenData = parseToken(token);
        console.log(tokenData)
        if (tokenData.exp && tokenData.exp < Date.now() / 1000) {
            logout();
        } else {
            loadUserProfile();
            document.querySelector('.login').style.display = 'none';
            document.querySelector('.crudPart').style.display = 'flex';
        }
    } else {
        document.querySelector('.login').style.display = 'flex';
        document.querySelector('.crudPart').style.display = 'none';
    }
});

//login
document.getElementById('loginButton').addEventListener('click', function (e) {
    e.preventDefault();
    document.getElementById('message').textContent = '';

    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;

    const userData = {
        username: username,
        password: password,
    };

    fetch(`${apiUrl}/api/auth/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData),
    }) 
    .then(response => {
        if (response.status === 401) {
            document.getElementById('message').textContent = 'Username or password is Error';
        } else if (response.ok) {
            return response.json();
        } else {
            throw new Error('Request failed');
        }
    })
    .then(data => {
        localStorage.setItem('memory', data.access_token);
        if(localStorage.getItem('memory')) {
            loadUserProfile();
            document.querySelector('.login').style.display ="none";
            document.querySelector('.crudPart').style.display = 'flex';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
})

// refresh
document.querySelector('.refresh').addEventListener('click', () => {
    document.getElementById('message').textContent = '';

    fetch(`${apiUrl}/api/auth/refresh`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('memory')}`
        },
    }) 
    .then(response => response.json())
    .then(data => {
        console.log(data);
        localStorage.setItem('memory', data.access_token);
        document.getElementById('message').textContent = 'Refresh Successful';

        setTimeout(() => {
            window.location.reload();
        }, 2000);
    })
    .catch(error => {
        console.error('Error:', error);
    });
})

// create
document.getElementById('registerButton').addEventListener('click', function (e) {
    e.preventDefault();
    document.getElementById('message').textContent = '';

    const username = document.getElementById('registerUsername').value;
    const password = document.getElementById('registerPassword').value;
    const birthday = document.getElementById('registerBirthday').value;

    const userData = {
        username: username,
        password: password,
        birthday: birthday,
    };

    fetch(`${apiUrl}/api/auth/register`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(userData),
    })
    .then(response => {
        if (response.status === 403) {
            document.getElementById('message').textContent = 'Username already exists';
        } else if (response.ok) {
            return response.json();
        } else {
            throw new Error('Request failed');
        }
    })
    .then(data => {
        if (data.message) {
            loginUser(username, password);
            document.getElementById('message').textContent = data.message;
            
            document.querySelector('.createUser').style.display = 'none';
            document.querySelector('.crudPart').style.display = 'flex';
        } else if (data.error) {
            document.getElementById('message').textContent = data.error;

            document.querySelector('.createUser').style.display = 'flex';
            document.querySelector('.crudPart').style.display = 'none';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.querySelector('.createUser').style.display = 'flex';
        document.querySelector('.crudPart').style.display = 'none';
    });
});

// 獲取DOM
const userTableAll = document.getElementById('userTableAll');
const tbodyAll = userTableAll.querySelector('tbody');
// search All
document.getElementById('getUser').addEventListener('click', () => {
    document.getElementById('message').textContent = '';
    fetch(`${apiUrl}/api/user`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('memory')}`
        },
    })
    .then(response => response.json())
    .then(data => {
        let userData = data.data;

        tbodyAll.innerHTML = '';

        userData.forEach(user => {
            const row = document.createElement('tr');

            console.log(localStorage.getItem('username')  == user.username,localStorage.getItem('username') , user.username)
            const deleteButtonHtml = localStorage.getItem('username') == user.username
                ? ''
                : '<td><button class="deleteUser">delete</button></dt>';
        
            row.innerHTML = `
                <td>${user.username}</td>
                <td>${user.birthday}</td>
                ${deleteButtonHtml}
            `;
           
            tbodyAll.appendChild(row);
            // delete
            const deleteButton = row.querySelector('.deleteUser');
            deleteButton.addEventListener('click', () => {
                const username = user.username;
                tbodyAll.removeChild(row);
                fetch(`${apiUrl}/api/user/${username}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('memory')}`
                    },
                })    
                .then(response => {   
                    return response.json();
                })
                .then(data => {
                    console.log("dtaa",data)
                    if (data.message) {
                        document.getElementById('message').textContent = data.message;
                    } else if (data.error) {
                        document.getElementById('message').textContent = data.error;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// 獲取DOM
const userTableByUsername = document.getElementById('userTableByUsername');
const tbodyByUsername = userTableByUsername.querySelector('tbody');
// search by username
document.getElementById('getUserByUN').addEventListener('click', () => {
    document.getElementById('message').textContent = '';
    const username = document.querySelector('.getUserByUsername input[name="username"]').value;

    fetch(`${apiUrl}/api/user/${username}`,{
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('memory')}`
        },
    })
    .then(response => response.json())
    .then(data => {
        let userData = data.data;
        tbodyByUsername.innerHTML = '';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${userData.username}</td>
            <td>${userData.birthday}</td>
        `;
        tbodyByUsername.appendChild(row);
        
    })
    .catch(error => {
        console.error('Error:', error);
    });
})

// updata
document.getElementById('updataUserForm').addEventListener('submit', function (e) {
    e.preventDefault();
    document.getElementById('message').textContent = '';

    const username = document.getElementById('userProfileName').textContent;
    const password = document.getElementById('userProfilepPssword').value;
    const birthday = document.getElementById('userProfileBirthday').value;

    const userData = {
        username: username,
        password: password,
        birthday: birthday,
    };

    fetch(`${apiUrl}/api/user/${username}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('memory')}`
        },
        body: JSON.stringify(userData),
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            document.getElementById('message').textContent = data.message;
        } else if (data.error) {
            document.getElementById('message').textContent = data.error;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// logout
function logout() {
    document.getElementById('message').textContent = '';

    fetch(`${apiUrl}/api/auth/logout`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('memory')}`
        },
    }) 
    .then(response => response.json())
    .then(data => {
        localStorage.setItem('memory', undefined);
        document.querySelector('.login').style.display = 'flex';
        document.querySelector('.crudPart').style.display = 'none';
        document.getElementById('message').textContent = 'Logout Successful';
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function loadUserProfile() {
    const userProfileName = document.getElementById('userProfileName');
    const passwordInput = document.getElementById('userProfilepPssword');
    const birthdayInput = document.getElementById('userProfileBirthday');
    
    userProfileName.textContent = '';
    passwordInput.value = '';
    birthdayInput.value = '';

    fetch(`${apiUrl}/api/auth/userProfile`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('memory')}`
        },
    })
    .then(response => {
        if (response.status === 401) {
            showTokenExpiredAlert();
        } else if (response.ok) {
            return response.json();
        } else {
            throw new Error('Request failed');
        }
    })
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            return;
        }
        console.log(data, userProfileName, passwordInput, birthdayInput)
        localStorage.setItem('username', data.username);
        userProfileName.textContent = data.username;
        passwordInput.value = data.password;
        birthdayInput.value = data.birthday;

        passwordInput.removeAttribute('readonly');
        birthdayInput.removeAttribute('readonly');
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function showTokenExpiredAlert() {
    alert('Your token has expired. Please log in again.');
    document.querySelector('.login').style.display = 'flex';
    document.querySelector('.crudPart').style.display = 'none';
}

function loginUser(username, password) {
    const loginData = {
        username: username,
        password: password,
    };

    fetch(`${apiUrl}/api/auth/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(loginData),
    }) 
    .then(response => response.json())
    .then(data => {
        localStorage.setItem('memory', data.access_token);
        if(localStorage.getItem('memory')) {
            loadUserProfile();
            document.querySelector('.login').style.display ="none";
            document.querySelector('.createUser').style.display = 'none';
            document.querySelector('.crudPart').style.display = 'flex';
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function toogleView(className) {
    document.getElementById('message').textContent = '';
    if(!!className) {
        document.querySelector('.login').style.display = 'none';
        document.querySelector('.createUser').style.display = 'flex';
    } else {
        document.querySelector('.login').style.display = 'flex';
        document.querySelector('.createUser').style.display = 'none';
    }

}