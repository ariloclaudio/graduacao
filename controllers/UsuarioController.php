<?php

namespace app\controllers;

use Yii;
use app\models\Usuario;
use app\models\UsuarioSearch;
use app\models\UsuarioForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
/**
 * UsuarioController implements the CRUD actions for Usuario model.
 */
class UsuarioController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'delete'],
                'rules' => [
                    [
                        'actions' => ['index', 'delete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if(!Yii::$app->user->isGuest)
                            {
                                return Yii::$app->user->identity->isAdmin == 1 ;    
                            }                            
                        }
                    ],
                ],
            ],            
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Usuario models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsuarioSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Usuario model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Usuario model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Usuario();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            
            $model->password = md5($model->password);
            
            $model->save();
            
            return $this->redirect(['view', 'id' => $model->id]);

        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Usuario model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {

    }

    /**
     * Deletes an existing Usuario model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Usuario model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Usuario the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Usuario::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    /* **** ***************************
    * Novo usuario via Webservice
    * ult.mod: 09/12/2015
    * *** ************************** */
    public function actionNovousuario()
    {

        if ( Yii::$app->request->post()) 
        {
            
            $model = new Usuario();

            //verifica se o aluno já está cadastrado
            //com o cpf informado...
            $usuario = Usuario::find()->where(['cpf' => Yii::$app->request->post('cpf') ])->one();

            if( $usuario != null )
            {
                return $this->render('novousuario', ['erro'=>'Usuário já cadastrado']);
            }

            /* * pega os dados do webservice do cpd * */
            $link = 'http://200.129.163.9:8080/ecampus/servicos/getPessoaValidaSIE?cpf=' ;
            
            $link = $link . Yii::$app->request->post('cpf');

            $webservice = @file_get_contents($link);
            
            // Caso o webservice esteja indisponivel o
            // sistema volta para a pagina inicial
            if($webservice == null)
            {
                return $this->goBack();
            }
            
            $dados = json_decode($webservice, true);

            //verifica se encontrou o CPF no ecampus
            if( isset( $dados['CPF inválido ']) )
            {
                return $this->render('novousuario', ['erro'=>'CPF não encontrado ou inválido']);
            }

            //Para alunos com mais de uma matricula
            //procura a matricula ativa
            //se nenhuma matrica estiver ativa.. o sistema volta a tela inicia
            $atual = null;
            
            foreach($dados as $aluno)
            {
                if($aluno["ATIVO"]=="true")
                {
                    $atual = $aluno;
                }
            }

            if($atual==null)
            {
                return $this->render('novousuario', ['erro'=>'O aluno não está ativo']);
            }
            
            // Adc os dados do aluno no model e redireciona p 
            // tela de cadastro...
            $model->name = $atual['NOME_PESSOA'] ;
            
            $model->cpf = Yii::$app->request->post('cpf');

            $model->email = $atual['EMAIL'];
            
            $model->matricula = $atual['MATR_ALUNO'];
            
            $model->isNewRecord = true; 

            return $this->render('create', ['model' => $model]);                
        }
        else
        {
            return $this->render('novousuario') ;  
        }

    }
    
    public function actionRecuperarsenha()
    {

        
        if ( Yii::$app->request->post()) 
        {
            $email = Yii::$app->request->post('email');

            $usuario = Usuario::find()->where(['email'=>$email])->one();

            if($usuario!=null) //se o usuario com email informado existe...
            {
                
                //gera o token de troca senha
                $usuario->generatePasswordResetToken();
                
                //prepara o email com o link
                $domain = 'sandbox081c87f9e07a4f669f46f26af7261c2a.mailgun.org';
                $key = 'key-f0dc85b59a45bcda5373019f605ce034';

                $mailgun = new \MailgunApi( $domain, $key );

                $message = $mailgun->newMessage();
                $message->setFrom('admin@icomp.ufam.edu.br', 'Admin-Atv Complementares');
                $message->addTo( $usuario->email, $usuario->name); //destinatario...
                $message->setSubject('Nova Senha');
                //$message->setText('Sua nova senha temporária é: ' . $usuario->senhaAleatoria() );
                $message->setHtml(
                    '<!doctype html>
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                        <title>Email Reset</title>
                        <style>
                            div {
                                width: 600px;
                                heigth: auto;
                                padding: 2px 2px 2px 2px;
                                border: 1px solid navy;
                                margin: 1px 1px 1px 1px;
                                
                                
                            }
                        </style>
                    </head>
                    <body>
                        <div>
                        <h2>Prezado: ' . $usuario->name . '</h2>
                        <p>Conforme solicitado segue abaixo o link para o troca senha:</p>
                        <h3>'. 
                        Url::to(['usuario/resetpassword', 'token' => $usuario->password_reset_token] , true) 
                        . '</h3>
                        </div></body></html>'
                );
                
                $message->send();

                return $this->render('senhaenviada');
            }
            else
            {
                return $this->render('emailnaoencontrado');
            }
        }
        else
        {
            return $this->render('recuperarsenha');
        }
    }
    
    /**
     * Reseta a senha do usuario informado.
     */
    public function actionResetpassword()
    {
        //busca o usuario pelo token
        $token = Yii::$app->request->get('token');

        $usuario = Usuario::find()->where(['password_reset_token'=>$token])->one();

        if($usuario==null)
        {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ( Yii::$app->request->post() ) 
        {
            
            //busca o usuario pelo token
            $token = Yii::$app->request->get('token');

            $usuario = Usuario::find()->where(['password_reset_token'=>$token])->one();
            
            if($usuario==null)
            {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
            
            $usuario->password = md5(Yii::$app->request->get('senhanova'));
            
            $usuario->save();
            
            return var_dump($usuario);

            return $this->goHome();
        }
        else
        {
            return $this->render('novasenha', ['model' => $usuario]);             
        }
      

    }
    
}
