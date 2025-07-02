// Espera o documento HTML ser completamente carregado antes de executar o script
document.addEventListener('DOMContentLoaded', function() {
    
    // Pega o botão de troca de tema pelo seu ID
    const themeToggle = document.getElementById('theme-toggle');
    
    // Pega o elemento <html> da página. É aqui que vamos adicionar/remover a classe .dark-mode
    const htmlElement = document.documentElement;

    // Se o botão não existir na página, não faz mais nada.
    if (!themeToggle) {
        return;
    }

    // Adiciona o evento de clique ao botão
    themeToggle.addEventListener('click', function() {
        // Alterna (adiciona se não tiver, remove se tiver) a classe 'dark-mode' no elemento <html>
        htmlElement.classList.toggle('dark-mode');
        
        // Salva a preferência do usuário no localStorage do navegador
        if (htmlElement.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
        } else {
            localStorage.setItem('theme', 'light');
        }
    });
});