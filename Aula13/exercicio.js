// Você recebeu uma lista de objetos representando alunos e suas respectivas notas em uma disciplina.

// Seu objetivo é analisar os resultados e obter informações estatísticas utilizando métodos de arrays (filter,
// map, reduce).

// Filtrar apenas os alunos aprovados, considerando nota maior ou igual a 7.

// Gerar um novo array contendo apenas as notas dos alunos aprovados.

// Calcular a média das notas dos aprovados.

// Exibir no console:

// A lista dos aprovados;

// As notas dos aprovados;

// A média final com duas casas decimais.

const alunos = [

    { nome: 'Diego', nota: 9.0 },

    { nome: 'Ana', nota: 6.5 },

    { nome: 'Lucas', nota: 7.2 },

    { nome: 'Mariana', nota: 8.3 },

    { nome: 'João', nota: 5.9 },

];


const aprovados = alunos.filter (aluno => aluno.nota >=7);
console.log("Alunos Aprovados", aprovados);
const notasAprovados = aprovados.map (aluno => aluno.nota);
console.log("Somente notas" , notasAprovados);
const media = (notasAprovados.reduce ((acum, valor) => acum + valor,0)/ notasAprovados.length).toFixed(2);
console.log("Média dos aprovados", media);
