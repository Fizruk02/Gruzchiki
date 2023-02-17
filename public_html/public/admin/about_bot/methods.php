<?php

class cl
{
    
    public function docs( $POST ){
        $id=setting('project_docs');

        return [
            'success'=> 'ok',
            'id'=> $id,
            'files'=> loadFiles()->getFilesforweb( $id ),
            'notes'=> setting('project_notes')
        ];
    }

    public function load( $POST ){
        query('DELETE FROM `settings` WHERE t_key="project_docs"');
        if($POST['id'])
        query('INSERT INTO `settings` (`t_key`, `value`, `name`, `visible`, `type`, `t_group`) VALUES ("project_docs",?,"", 0, "files", "basic")', [ $POST['id'] ]);
        return ['success'=> 'ok'];
    }

    public function notes( $POST ){
        query('DELETE FROM `settings` WHERE t_key="project_notes"');
        if($POST['t'])
            query('INSERT INTO `settings` (`t_key`, `value`, `name`, `visible`, `type`, `t_group`) VALUES ("project_notes",?,"", 0, "text", "basic")', [ $POST['t'] ]);
        return ['success'=> 'ok'];
    }
}

















