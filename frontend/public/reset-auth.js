// Script para limpar a autenticação
localStorage.removeItem('token');
localStorage.removeItem('user');
window.location.href = '/login';