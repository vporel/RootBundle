<?php
namespace RootBundle\Controller\Admin;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController as EA_AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\FileUploadType;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use UserAccountBundle\Entity\User;

abstract class AbstractCrudController extends EA_AbstractCrudController
{
    protected function getAdmin(): User{
        return $this->getUser();
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = ["id" => IdField::new("id", "#ID")->hideOnForm()];
        $onlyFieldsWhenUpdating = $this->onlyFieldsWhenUpdating();
        foreach(parent::configureFields($pageName) as $field){
            $property = $field->getAsDto()->getProperty();
            if(in_array($property, $this->fieldsToHide()))
                continue;
            if((count($onlyFieldsWhenUpdating) > 0) && !in_array($property, $onlyFieldsWhenUpdating))
                $field = $field->hideWhenUpdating();
            if(in_array($property, $this->fieldsToHideOnIndexAndForm())){
                $field = $field->hideOnForm()->hideOnIndex();
            }
            if(in_array($property, $this->fieldsToHideOnForm()))
                $field = $field->hideOnForm();
            if(in_array($property, $this->fieldsToHideOnIndex()))
                $field = $field->hideOnIndex();
            $fields[$property] = $field;
        };
        return $fields;
    }

    /**
     * fields to hide on index on forms
     * @return array
     */
    protected function fieldsToHideOnIndexAndForm():array
    {
        return [];
    }

    protected function fieldsToHideOnIndex():array
    {
        return [];
    }

    protected function fieldsToHideOnForm():array
    {
        return ["createdAt", "updatedAt", "expiresAt", "deletedAt", "sendAt", "seenAt", "seen"];
    }

    /**
     * If the returned array is not empty, only the fields in it will be shown ; the others will be hidden
     */
    protected function onlyFieldsWhenUpdating(): array{
        return [];
    }

    /**
     * Fields to hide on all pages
     */
    protected function fieldsToHide(){
        return ["slug"];
    }

    /**
     * Remove some field from a fields array
     * @param iterable $fields
     * @param ?string $pageName "index" | "form" | "detail" | null for all
     * @param string ...$fieldsToRemove
     * 
     * @return iterable
     */
    protected function filterFieldsArray(iterable $fields, ?string $pageName = null, string ...$fieldsToRemove): iterable{
        $filteredFields = [];
        foreach($fields as $field){
            $property = $field->getAsDto()->getProperty();
            if(in_array($property, $fieldsToRemove)){
                if($pageName == "index"){
                    $field->hideOnIndex();
                }elseif($pageName == "form"){
                    $field->hideOnForm();
                }elseif($pageName == "detail"){
                    $field->hideOnDetail();
                }else{
                    $field->hideOnForm();
                    $field->hideOnIndex();
                    $field->hideOnDetail();
                }
            }
            $filteredFields[] = $field;
        };
        return $filteredFields;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entity): void
    {
        if(method_exists($entity, "softDelete"))
            $entity->softDelete();
        else
            $entityManager->remove($entity);
        $entityManager->flush();
    }
    
    /**
     * Format the set of elements a associated collection
     * if $page == index, only the number of elements will be shown
     * If you want to always show the elements strings, make $pageName = null
     * @param ?string $pageName The current page name
     * @param int $entityId
     * @param mixed $elementsCount
     * @param mixed $elements
     * 
     * @return [type]
     */
    protected function formatAssociationFieldValue(?string $pageName, int $entityId, $elementsCount, $elements){
        if($pageName == Crud::PAGE_INDEX){ 
            $detailUrl = $this->container->get(AdminUrlGenerator::class)
                ->setAction(Action::DETAIL)
                ->setEntityId($entityId)
                ->generateUrl();
            return ($elementsCount == 0) ? $elementsCount : '<a href="' . $detailUrl . '">' . $elementsCount . '</a>';
        }
        if($elements instanceof Collection)
            $elements = $elements->toArray();
        return $elementsCount > 0 ? implode(", ", $elements) : "Aucun(e)";
    }

    protected function renderView(string $template, array $data = []): string
    {
        if(!str_ends_with($template, ".html.twig")) $template = $template . ".html.twig";
        $data = array_merge($data, ["admin" => $this->getAdmin()]);
        return parent::renderView($template, $data);
    }

    /**
     * @param string $uploadDir
     * @param string $propertyName
     * @param string|null $label
     * @param array $extensions
     * @return Field
     */
    protected function newFileField(string $uploadDir, string $propertyName, ?string $label = null, $extensions = [".jpg", ".jpeg", ".png", ".mp4", ".pdf", ".docx", ".doc"]): Field
    {
        return Field::new($propertyName, $label)
            ->setFormType(FileUploadType::class)
            ->setFormTypeOption("upload_filename", "[name]-[timestamp].[extension]")
            ->setFormTypeOption("upload_dir", $this->getParameter("public_dir") . $uploadDir)
            ->setFormTypeOption("attr", [
                "accept" => implode(", ", $extensions)
            ])
        ;
    }


    /**
     * Undocumented function
     *
     * @param string $uploadDir
     * @param string $propertyName
     * @param ?string $label
     * @param array $extensions With the dots
     * @return ImageField
     */
    protected function newImageField(string $uploadDir, string $propertyName, ?string $label = null, $extensions = [".jpg", ".jpeg", "gif", ".png"]): ImageField
    {
        return ImageField::new($propertyName, $label)
            ->setUploadedFileNamePattern("[name]-[timestamp].[extension]")
            ->setUploadDir($this->getParameter("relative_public_dir") . $uploadDir)
            ->formatValue(function($value) use($uploadDir){
                return $uploadDir . "/" . $value;
            })
            ->setFormTypeOption("attr", [
                "accept" => implode(", ", $extensions)
            ])
        ;
    }
}
