// Crie um script em JavaScript que leia uma lista de usuários no formato JSON (string).

//  O programa deve:

// Tentar converter o JSON em objeto.

// Verificar se cada usuário possui os campos obrigatórios nome e idade.

// Lançar exceções personalizadas caso:

// O JSON seja inválido.

// Faltarem campos obrigatórios.

// A idade não seja um número válido.

// Exibir mensagens apropriadas no catch, diferenciando o tipo de erro.

// O bloco finally deve exibir a mensagem "Processamento finalizado."


// Utilize os seguinte dados

const dados = `[

  {"nome": "Ana", "idade": 25},

  {"nome": "Carlos"},

  {"nome": "Marina", "idade": "vinte"},

  {"nome": "Diego", "idade": 39}

]`;

const objeto = JSON.parse(dados);

console.log(objeto);

console.log(objeto[0].nome); //COLOCAR [i]

console.log(objeto[0].idade)// COLOCAR [i]

