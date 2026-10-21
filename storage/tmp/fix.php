<?php
$p='app/Services/Import/SourceImporter.php';
$s=file_get_contents($p);
$a=strpos($s,"        Schema::withoutForeignKeyConstraints(function () use (\$tables");
$b=strpos($s,"        if (in_array('questions', \$tables, true)) {\n            \$this->afterQuestionsImported");
$block=substr($s,$a,$b-$a);
$block=str_replace("        if (\$localAccounts !== []) {\n            \$this->restoreLocalAccounts(\$localAccounts);\n        }\n\n","",$block);
$indented=preg_replace('/^(?=.)/m','    ',rtrim($block));
$new="        try {\n".$indented."\n        } finally {\n            // Also when the copy fails halfway: never lose a local dashboard login.\n            if (\$localAccounts !== []) {\n                \$this->restoreLocalAccounts(\$localAccounts);\n            }\n        }\n\n";
$s=substr($s,0,$a).$new.substr($s,$b);
file_put_contents($p,$s);
