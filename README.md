# Simulador Online de Algoritmos Para Escalonamento de Processos

## Sobre o simulador

O objetivo geral deste simulador é demonstrar o funcionamento dos algoritmos de escalonamento de processos First-in, First-out (FIFO) , Round Robin (RR) , Shortest Job First (SJF) , Shortest Job Remaining Time First (SJRT) e Escalonamento por prioridades (PRIOc, PRIOp), tal que o mesmo seja de fácil acesso tanto por alunos quanto por professores, os dados obtidos com as demonstrações do funcionamento dos algoritmos poderá ser visualizado através de gráficos ilustrativos.

## Como instalar

É necessário instalar o [PHP 8](https://windows.php.net/download/) e o [Composer](https://getcomposer.org/download/). Após a instalação do PHP e Composer, é necessário clonar o projeto através do seguinte comando:

```sh
git clone https://github.com/igorpsantos/sodap.git
cd /sodap
```

Após clonar o repositório será necessário criar o arquivo .env da aplicação.

```sh
cp .env.example .env
```

Após executado o comando, basta rodar o projeto em localhost através do comando:

```sh
php artisan serve
```

Por padrão, como utilizamos o framework Laravel, o comando acima irá rodar a aplicação na porta 8000.

## Manual para uso

O manual de uso da aplicação está disponível no link abaixo:

[Download](https://docs.google.com/uc?export=download&id=1geqa1YrioLUhH-Bl45nHNcjyOpbx1Mjk)
